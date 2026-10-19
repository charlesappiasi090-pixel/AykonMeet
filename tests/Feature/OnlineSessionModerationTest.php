<?php

namespace Tests\Feature;

use App\Mail\AttendeeInvitation;
use App\Mail\OnlineSessionVerificationCode;
use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\MeetingRoom;
use App\Models\OnlineSessionParticipant;
use App\Models\OnlineSessionRecording;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OnlineSessionModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_can_mute_and_unmute_a_guest_but_other_users_cannot(): void
    {
        Mail::fake();

        $host = User::factory()->create();
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-MUTE1',
            'name' => $host->name,
            'email' => $host->email,
            'phone' => '555-0100',
            'meeting_topic' => 'Moderation session',
            'start_date' => now(),
            'end_date' => now()->addHour(),
            'status' => 'in_progress',
            'is_online' => true,
            'host_user_id' => $host->id,
            'settings' => ['waiting_room' => false],
        ]);
        $joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]);
        $heartbeatUrl = URL::signedRoute('sessions.participants.heartbeat', ['meeting' => $meeting]);
        $muteUrl = URL::signedRoute('sessions.participants.mute', ['meeting' => $meeting]);
        $attendee = $meeting->attendees()->create([
            'full_name' => 'Guest attendee',
            'email' => 'guest@example.test',
            'phone' => '555-0101',
        ]);

        $this->actingAs($host)
            ->get(URL::signedRoute('sessions.join', ['meeting' => $meeting]))
            ->assertOk()
            ->assertSee('Participants')
            ->assertDontSee('Copy invite link')
            ->assertSee('data-is-host="true"', false);

        $this->postJson($joinUrl, ['email' => 'wrong-host@example.test'])
            ->assertForbidden();

        $hostJoin = $this->postJson($joinUrl, ['email' => $host->email])->assertOk()->json();
        $this->assertTrue($hostJoin['is_host']);

        $verificationCode = $this->requestEmailVerificationCode($meeting, $attendee);
        $guestJoin = $this->actingAs(User::factory()->create())
            ->postJson($joinUrl, ['email' => $attendee->email, 'verification_code' => $verificationCode])
            ->assertOk()
            ->json();
        $guestPeerId = $guestJoin['peer_id'];
        $this->assertSame('Guest attendee', OnlineSessionParticipant::where('peer_id', $guestPeerId)->value('display_name'));

        $this->postJson($muteUrl, ['peer_id' => $guestPeerId, 'muted' => true])
            ->assertForbidden();

        $this->actingAs($host)
            ->postJson($muteUrl, ['peer_id' => $guestPeerId, 'muted' => true])
            ->assertOk();

        $guestState = $this->actingAs(User::factory()->create())
            ->postJson($heartbeatUrl, ['peer_id' => $guestPeerId, 'token' => $guestJoin['token']])
            ->assertOk()
            ->json();
        $this->assertTrue($guestState['is_muted']);
        $this->assertTrue(OnlineSessionParticipant::where('peer_id', $guestPeerId)->value('is_muted_by_host'));

        $this->actingAs($host)
            ->postJson($muteUrl, ['peer_id' => $guestPeerId, 'muted' => false])
            ->assertOk();

        $guestState = $this->actingAs(User::factory()->create())
            ->postJson($heartbeatUrl, ['peer_id' => $guestPeerId, 'token' => $guestJoin['token']])
            ->assertOk()
            ->json();
        $this->assertFalse($guestState['is_muted']);
    }

    public function test_guest_must_verify_their_invited_email_before_joining_and_checking_in(): void
    {
        Mail::fake();

        $meeting = Meeting::create([
            'meeting_code' => 'AYK-VERIFY',
            'name' => 'Meeting host',
            'email' => 'host@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Invitation only room',
            'start_date' => now(),
            'end_date' => now()->addHour(),
            'status' => 'in_progress',
            'is_online' => true,
        ]);
        $attendee = $meeting->attendees()->create([
            'full_name' => 'Invited attendee',
            'email' => 'invited@example.test',
            'phone' => '555-0101',
        ]);
        $this->get(URL::signedRoute('sessions.join', ['meeting' => $meeting]))
            ->assertOk()
            ->assertSee('Joining the meeting')
            ->assertSee('data-direct-join=', false)
            ->assertSee('data-is-host="false"', false)
            ->assertDontSee('Copy invite link');
        $sendCodeUrl = URL::signedRoute('sessions.participants.send-code', ['meeting' => $meeting]);
        $joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]);

        $this->postJson($sendCodeUrl, ['email' => 'not-invited@example.test'])
            ->assertOk()
            ->assertJsonPath('message', 'If this email is invited, a verification code has been sent.');
        Mail::assertNothingSent();

        $this->postJson($joinUrl, ['email' => $attendee->email, 'verification_code' => '123456'])
            ->assertStatus(422);
        $this->assertNull($attendee->fresh()->checked_in_at);
        $this->assertDatabaseMissing('online_session_participants', ['attendee_id' => $attendee->id]);

        $verificationCode = $this->requestEmailVerificationCode($meeting, $attendee);
        $this->postJson($joinUrl, ['email' => $attendee->email, 'verification_code' => '000000'])
            ->assertStatus(422);

        $join = $this->postJson($joinUrl, [
            'email' => strtoupper($attendee->email),
            'verification_code' => $verificationCode,
        ])->assertOk()
            ->json();
        $this->assertSame('Invited attendee', OnlineSessionParticipant::where('peer_id', $join['peer_id'])->value('display_name'));
        $this->assertNotNull($attendee->fresh()->checked_in_at);
        $this->assertDatabaseMissing('online_session_email_verifications', ['attendee_id' => $attendee->id]);

        $this->postJson($joinUrl, ['email' => $attendee->email, 'verification_code' => $verificationCode])
            ->assertStatus(422);
    }

    public function test_verification_code_expires_and_is_limited_to_five_attempts(): void
    {
        Mail::fake();

        $meeting = Meeting::create([
            'meeting_code' => 'AYK-CODE1',
            'name' => 'Meeting host',
            'email' => 'host@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Verification limits',
            'start_date' => now(),
            'end_date' => now()->addHour(),
            'status' => 'in_progress',
            'is_online' => true,
        ]);
        $attendee = $meeting->attendees()->create([
            'full_name' => 'Invited attendee',
            'email' => 'invited@example.test',
            'phone' => '555-0101',
        ]);
        $joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]);
        $code = $this->requestEmailVerificationCode($meeting, $attendee);
        $wrongCode = $code === '999999' ? '000000' : '999999';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson($joinUrl, [
                'email' => $attendee->email,
                'verification_code' => $wrongCode,
            ])->assertStatus(422);
        }

        $this->postJson($joinUrl, [
            'email' => $attendee->email,
            'verification_code' => $code,
        ])->assertStatus(422);
        $this->assertDatabaseHas('online_session_email_verifications', [
            'attendee_id' => $attendee->id,
            'attempts' => 5,
        ]);
        $this->assertNull($attendee->fresh()->checked_in_at);

        $freshCode = $this->requestEmailVerificationCode($meeting, $attendee);
        $this->travel(11)->minutes();
        $this->postJson($joinUrl, [
            'email' => $attendee->email,
            'verification_code' => $freshCode,
        ])->assertStatus(422);
        $this->assertNull($attendee->fresh()->checked_in_at);
    }

    public function test_adding_an_online_attendee_emails_the_generated_code_and_join_link(): void
    {
        Mail::fake();

        $meeting = Meeting::create([
            'meeting_code' => 'AYK-EMAIL1',
            'name' => 'Meeting host',
            'email' => 'host@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Emailed online invitation',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
            'is_online' => true,
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('attendees.store', $meeting), [
                'full_name' => 'Invited attendee',
                'email' => 'invited@example.test',
                'phone' => '5550101',
                'mark_present' => '0',
            ])
            ->assertSessionHas('success');

        $attendee = $meeting->attendees()->firstOrFail();
        $this->assertNotEmpty($attendee->attendee_code);

        \Log::info('Test: Checking sent mail', ['sent_count' => Mail::sent(AttendeeInvitation::class)->count()]);

        Mail::assertSent(AttendeeInvitation::class, function (AttendeeInvitation $mail) use ($attendee, $meeting) {
            \Log::info('Mail assertSent callback called');
            try {
                $body = $mail->render();
                \Log::info('Mail rendered successfully', ['html_length' => strlen($body)]);

                $checks = [
                    'hasTo' => $mail->hasTo($attendee->email),
                    'hasAttendeeCode' => str_contains($body, $attendee->attendee_code),
                    'hasViewInvitation' => str_contains($body, 'Join Meeting'),
                    'hasInvitationToken' => str_contains($body, $attendee->invitation_token),
                    'hasMeetingCode' => str_contains($body, $meeting->meeting_code),
                ];

                \Log::info('Render checks', $checks);

                return $checks['hasTo']
                    && $checks['hasAttendeeCode']
                    && $checks['hasViewInvitation']
                    && $checks['hasInvitationToken']
                    && $checks['hasMeetingCode'];
            } catch (\Throwable $e) {
                \Log::error('Error in mail callback', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return false;
            }
        });
    }

    public function test_online_room_tracks_attendance_chat_signals_hands_and_recordings(): void
    {
        Mail::fake();
        Storage::fake('local');
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-ROOM01',
            'name' => 'Meeting host',
            'email' => 'host@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Room features',
            'start_date' => now(),
            'end_date' => now()->addHour(),
            'status' => 'in_progress',
            'is_online' => true,
            'settings' => ['waiting_room' => false],
        ]);
        $firstAttendee = $meeting->attendees()->create([
            'full_name' => 'First attendee',
            'email' => 'first@example.test',
            'phone' => '555-0101',
        ]);
        $secondAttendee = $meeting->attendees()->create([
            'full_name' => 'Second attendee',
            'email' => 'second@example.test',
            'phone' => '555-0102',
        ]);
        $joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]);
        $firstCode = $this->requestEmailVerificationCode($meeting, $firstAttendee);
        $first = $this->postJson($joinUrl, [
            'email' => $firstAttendee->email,
            'verification_code' => $firstCode,
        ])->assertOk()->json();
        $this->assertNotNull($firstAttendee->refresh()->checked_in_at);
        $secondCode = $this->requestEmailVerificationCode($meeting, $secondAttendee);
        $second = $this->postJson($joinUrl, [
            'email' => $secondAttendee->email,
            'verification_code' => $secondCode,
        ])->assertOk()->json();
        $this->assertSame($first['peer_id'], $second['participants'][0]['peer_id']);

        $auth = ['peer_id' => $first['peer_id'], 'token' => $first['token']];
        $this->postJson(URL::signedRoute('sessions.chat.send', ['meeting' => $meeting]), [
            'peer_id' => $first['peer_id'],
            'token' => str_repeat('x', 64),
            'message' => 'Should not be accepted',
        ])->assertForbidden();

        $this->postJson(URL::signedRoute('sessions.signals.send', ['meeting' => $meeting]), $auth + [
            'to_peer' => $second['peer_id'],
            'type' => 'offer',
            'payload' => ['type' => 'offer', 'sdp' => 'test-sdp'],
        ])->assertOk();

        $this->postJson(URL::signedRoute('sessions.chat.send', ['meeting' => $meeting]), $auth + [
            'message' => 'Welcome to the session',
        ])->assertCreated();
        $this->postJson(URL::signedRoute('sessions.transcript.store', ['meeting' => $meeting]), $auth + [
            'text' => 'We decided to approve the launch plan. Kofi will follow up with the team.',
        ])->assertCreated();
        $this->postJson(URL::signedRoute('sessions.hand', ['meeting' => $meeting]), $auth + [
            'raised' => true,
        ])->assertOk();
        $this->postJson(URL::signedRoute('sessions.transcript.store', ['meeting' => $meeting]), [
            'peer_id' => $first['peer_id'],
            'token' => str_repeat('x', 64),
            'text' => 'This transcript must be rejected.',
        ])->assertForbidden();

        $secondAuth = ['peer_id' => $second['peer_id'], 'token' => $second['token']];
        $secondState = $this->postJson(URL::signedRoute('sessions.participants.heartbeat', ['meeting' => $meeting]), $secondAuth)
            ->assertOk()
            ->json();
        $this->assertSame('offer', $secondState['signals'][0]['type']);
        $this->assertSame('Welcome to the session', $secondState['messages'][0]['message']);
        $this->assertTrue(OnlineSessionParticipant::where('peer_id', $first['peer_id'])->value('hand_raised'));

        $recording = $this->postJson(URL::signedRoute('sessions.recording.start', ['meeting' => $meeting]), $auth)
            ->assertCreated()
            ->json();

        $participant = OnlineSessionParticipant::where('peer_id', $first['peer_id'])->firstOrFail();

        // Finish the recording before leaving (same participant who started it)
        $this->post(URL::signedRoute('sessions.recording.finish', ['meeting' => $meeting]), $auth + [
            'recording_id' => $recording['recording_id'],
            'duration_seconds' => 42,
            'recording' => UploadedFile::fake()->create('session.webm', 12, 'video/webm'),
        ])->assertOk();

        // Now the first participant leaves
        $this->postJson(URL::signedRoute('sessions.participants.leave', ['meeting' => $meeting]), $auth)
            ->assertOk();
        $this->assertNotNull($participant->fresh()->left_at);
        $this->assertNotNull($firstAttendee->refresh()->checked_out_at);

        $admin = User::factory()->create();
        $this->actingAs($admin)
            ->post(route('meetings.end', $meeting))
            ->assertRedirect();
        $this->assertNotNull(OnlineSessionParticipant::where('peer_id', $second['peer_id'])->value('left_at'));
        $this->assertNotNull($secondAttendee->refresh()->checked_out_at);

        $recordingModel = OnlineSessionRecording::findOrFail($recording['recording_id']);
        Storage::disk('local')->assertExists($recordingModel->storage_path);
        $this->get(route('sessions.index'))
            ->assertOk()
            ->assertSee('Session reports')
            ->assertSee(route('sessions.report', $meeting), false);
        $this->actingAs($admin)
            ->get(route('sessions.report', $meeting))
            ->assertOk()
            ->assertSee('Attendance')
            ->assertSee('Session recordings')
            ->assertSee('Chat transcript')
            ->assertSee('Session Assistant')
            ->assertSee('Transcript-based draft')
            ->assertSee('Possible decisions')
            ->assertSee('Possible action items')
            ->assertSee('We decided to approve the launch plan.')
            ->assertSee('Kofi will follow up with the team.')
            ->assertSee('Welcome to the session')
            ->assertSee('Download');

        $this->get(route('sessions.recordings.download', [$meeting, $recordingModel]))->assertOk();
        $this->delete(route('sessions.recordings.delete', [$meeting, $recordingModel]))
            ->assertRedirect();
        $this->assertDatabaseMissing('online_session_recordings', ['id' => $recordingModel->id]);
        Storage::disk('local')->assertMissing($recordingModel->storage_path);
    }

    public function test_hybrid_meeting_requires_verified_rsvp_and_exposes_live_roster_only_to_organizers(): void
    {
        Mail::fake();

        $room = MeetingRoom::create([
            'name' => 'Innovation Hall',
            'number' => 'B-204',
            'floor' => '2',
            'location' => '24 Market Street',
        ]);
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-HYBRID',
            'name' => 'Hybrid host',
            'email' => 'host@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Hybrid planning session',
            'start_date' => now(),
            'end_date' => now()->addHour(),
            'status' => 'scheduled',
            'is_online' => true,
            'meeting_type' => 'hybrid',
            'meeting_room_id' => $room->id,
            'settings' => ['waiting_room' => false],
        ]);

        $onlineUrl = URL::signedRoute('sessions.join', ['meeting' => $meeting]);
        $inPerson = $meeting->attendees()->create([
            'full_name' => 'In Person Guest',
            'email' => 'person@example.test',
            'phone' => '555-0101',
        ]);
        $online = $meeting->attendees()->create([
            'full_name' => 'Online Guest',
            'email' => 'online@example.test',
            'phone' => '555-0102',
        ]);

        $this->get(route('meetings.show', $meeting->meeting_code))
            ->assertOk()
            ->assertSee('Hybrid session')
            ->assertSee('Attend Online')
            ->assertSee('Attend In Person')
            ->assertSeeInOrder(['Attend Online', 'Attend In Person', 'Confirm your attendance choice'])
            ->assertSee('value="online" form="hybridRsvp" required', false)
            ->assertSee("const selectedAttendanceMode=()=>root.querySelector('input[name=\"attendance_mode\"]:checked')?.value", false)
            ->assertSee('Email me a verification code')
            ->assertSee('id="resendHybridCode" type="button" hidden>Resend code', false)
            ->assertSee('Join Online')
            ->assertSee('id="confirmHybridRsvp" type="submit">Confirm RSVP', false)
            ->assertSee('id="hybridRsvpActions" class="meeting-actions"', false)
            ->assertSee('id="joinHybridOnline" href="'.$onlineUrl.'">', false)
            ->assertSee('id="changeHybridRsvp" type="button">Change attendance choice', false)
            ->assertSee('Confirm your attendance choice')
            ->assertSee('Online Meeting')
            ->assertSee('Continue to Online Meeting')
            ->assertSee('Innovation Hall')
            ->assertSee('B-204')
            ->assertSee('24 Market Street')
            ->assertSee('href="'.$onlineUrl.'"', false)
            ->assertDontSee('Online Guest')
            ->assertDontSee('In Person Guest')
            ->assertDontSee('capacity')
            ->assertDontSee('Copy invite link');

        $inPersonCode = $this->requestEmailVerificationCode($meeting, $inPerson);
        $this->postJson(route('meetings.hybrid-rsvp', $meeting), [
            'email' => $inPerson->email,
            'verification_code' => $inPersonCode,
            'attendance_mode' => 'in_person',
        ])->assertOk()
            ->assertJsonPath('attendance_mode', 'in_person')
            ->assertJsonMissingPath('presence');
        $this->assertSame('in_person', $inPerson->fresh()->attendance_mode);

        $onlineCode = $this->requestEmailVerificationCode($meeting, $online);
        $this->postJson(route('meetings.hybrid-rsvp', $meeting), [
            'email' => $online->email,
            'verification_code' => $onlineCode,
        ])->assertUnprocessable()->assertJsonValidationErrors('attendance_mode');
        $onlineCode = $this->requestEmailVerificationCode($meeting, $online);
        $this->postJson(route('meetings.hybrid-rsvp', $meeting), [
            'email' => $online->email,
            'verification_code' => $onlineCode,
            'attendance_mode' => 'online',
        ])->assertOk()
            ->assertJsonPath('attendance_mode', 'online')
            ->assertJsonMissingPath('presence');
        $onlineJoinCode = $this->requestEmailVerificationCode($meeting, $online);
        $joined = $this->postJson(URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]), [
            'email' => $online->email,
            'verification_code' => $onlineJoinCode,
        ])->assertOk()->json();
        $this->assertSame('online', $online->fresh()->attendance_mode);
        $this->get($onlineUrl)
            ->assertOk()
            ->assertSee('HYBRID SESSION')
            ->assertSee('Hybrid planning session')
            ->assertSee('Online Participants')
            ->assertSee('In-Person Participants')
            ->assertSee('Physical Meeting Location')
            ->assertSee('24 Market Street')
            ->assertSee('Mute')
            ->assertSee('Camera')
            ->assertSee('Share')
            ->assertSee('Chat')
            ->assertSee('People')
            ->assertSee('Leave Meeting')
            ->assertSee('id="sendVerificationCode" type="button"', false)
            ->assertSee('Verify your email here to receive a code', false)
            ->assertSee('id="resendVerificationCode" type="button" hidden>Resend code', false);
        $this->get(route('meetings.show', $meeting->meeting_code))
            ->assertOk()
            ->assertSee('Attend Online')
            ->assertSee('Verify your invited email')
            ->assertDontSee('Joining online')
            ->assertDontSee('Attending in person')
            ->assertDontSee('Not responded')
            ->assertDontSee('Attendance summary');

        $inPerson->update(['checked_in_at' => now()]);
        $this->getJson(route('meetings.hybrid-room-summary', $meeting))
            ->assertOk()
            ->assertJsonPath('online_count', 1)
            ->assertJsonPath('in_person_count', 1)
            ->assertJsonMissing(['name' => 'In Person Guest']);
        $this->getJson(route('meetings.hybrid-attendance', $meeting))->assertUnauthorized();
        $host = User::factory()->nonAdmin()->create();
        $meeting->update(['host_user_id' => $host->id]);
        $this->actingAs($host)->getJson(route('meetings.hybrid-attendance', $meeting))
            ->assertForbidden();
        $admin = User::factory()->create();
        $this->actingAs($admin)->getJson(route('meetings.hybrid-attendance', $meeting))
            ->assertOk()
            ->assertJsonPath('online.0.name', 'Online Guest')
            ->assertJsonPath('in_person.0.name', 'In Person Guest')
            ->assertJsonPath('not_responded_count', 0);
        $this->actingAs($host)->get(route('meetings.show', $meeting->meeting_code))
            ->assertOk()
            ->assertDontSee('hybridOrganizerAttendance')
            ->assertDontSee('Online Participants')
            ->assertDontSee('Joining online');
        $this->actingAs($admin)->get(route('meetings.show', $meeting->meeting_code))
            ->assertOk()
            ->assertSee('Online Guest');
        auth()->logout();
        $this->get(route('meetings.show', $meeting->meeting_code))
            ->assertOk()
            ->assertSee('Attend Online')
            ->assertDontSee('Online Participants')
            ->assertDontSee('In-Person Participants')
            ->assertDontSee('Not Responded')
            ->assertDontSee('Online Guest')
            ->assertDontSee('In Person Guest')
            ->assertDontSee('Hybrid Session Overview')
            ->assertDontSee('Shareable RSVP Page')
            ->assertDontSee('Copy Online Link')
            ->assertSee('id="joinHybridOnline"', false);

        $changeCode = $this->requestEmailVerificationCode($meeting, $online);
        $this->postJson(route('meetings.hybrid-rsvp', $meeting), [
            'email' => $online->email,
            'verification_code' => $changeCode,
            'attendance_mode' => 'in_person',
        ])->assertOk()->assertJsonPath('attendance_mode', 'in_person');
        $this->assertSame('in_person', $online->fresh()->attendance_mode);

        $meeting->update(['status' => 'in_progress', 'started_at' => now()]);
        $afterStartCode = $this->requestEmailVerificationCode($meeting, $online);
        $this->postJson(route('meetings.hybrid-rsvp', $meeting), [
            'email' => $online->email,
            'verification_code' => $afterStartCode,
            'attendance_mode' => 'online',
        ])->assertStatus(409);

        $this->postJson(URL::signedRoute('sessions.participants.leave', ['meeting' => $meeting]), [
            'peer_id' => $joined['peer_id'],
            'token' => $joined['token'],
        ])->assertOk();
    }

    public function test_verified_hybrid_participant_is_marked_online_when_joining_without_prior_rsvp(): void
    {
        Mail::fake();

        $room = MeetingRoom::create([
            'name' => 'Hybrid room',
            'location' => 'Innovation Hall',
            'number' => 'B-204',
            'floor' => '2',
            'capacity' => 10,
            'is_active' => true,
        ]);
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-HYBJ01',
            'name' => 'Host',
            'email' => 'host@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Hybrid join session',
            'start_date' => now(),
            'end_date' => now()->addHour(),
            'status' => 'scheduled',
            'is_online' => true,
            'meeting_type' => 'hybrid',
            'meeting_room_id' => $room->id,
        ]);
        $attendee = $meeting->attendees()->create([
            'full_name' => 'New Online Guest',
            'email' => 'new-online@example.test',
            'phone' => '555-0103',
        ]);
        $code = $this->requestEmailVerificationCode($meeting, $attendee);

        $this->postJson(URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]), [
            'email' => $attendee->email,
            'verification_code' => $code,
        ])->assertOk();

        $this->assertSame('online', $attendee->fresh()->attendance_mode);
        $this->assertNotNull($attendee->fresh()->checked_in_at);
    }

    public function test_joining_online_changes_an_in_person_rsvp_before_the_meeting_starts(): void
    {
        Mail::fake();

        $room = MeetingRoom::create([
            'name' => 'Hybrid room',
            'location' => 'Innovation Hall',
            'number' => 'B-204',
            'floor' => '2',
            'capacity' => 10,
            'is_active' => true,
        ]);
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-HYBJ02',
            'name' => 'Host',
            'email' => 'host@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Hybrid RSVP change session',
            'start_date' => now(),
            'end_date' => now()->addHour(),
            'status' => 'scheduled',
            'is_online' => true,
            'meeting_type' => 'hybrid',
            'meeting_room_id' => $room->id,
        ]);
        $attendee = $meeting->attendees()->create([
            'full_name' => 'Switching Guest',
            'email' => 'switching@example.test',
            'phone' => '555-0104',
            'attendance_mode' => 'in_person',
        ]);
        $code = $this->requestEmailVerificationCode($meeting, $attendee);

        $this->postJson(URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]), [
            'email' => $attendee->email,
            'verification_code' => $code,
        ])->assertOk();

        $this->assertSame('online', $attendee->fresh()->attendance_mode);
    }

    public function test_resending_hybrid_verification_code_issues_a_new_code_and_invalidates_the_old_one(): void
    {
        Mail::fake();

        $room = MeetingRoom::create([
            'name' => 'Hybrid room',
            'location' => 'Innovation Hall',
            'number' => 'B-204',
            'floor' => '2',
            'capacity' => 10,
            'is_active' => true,
        ]);
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-HYBJ03',
            'name' => 'Host',
            'email' => 'host@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Hybrid resend session',
            'start_date' => now(),
            'end_date' => now()->addHour(),
            'status' => 'scheduled',
            'is_online' => true,
            'meeting_type' => 'hybrid',
            'meeting_room_id' => $room->id,
        ]);
        $attendee = $meeting->attendees()->create([
            'full_name' => 'Resend Guest',
            'email' => 'resend@example.test',
            'phone' => '555-0105',
        ]);
        $sendCodeUrl = URL::signedRoute('sessions.participants.send-code', ['meeting' => $meeting]);

        $this->postJson($sendCodeUrl, ['email' => $attendee->email])->assertOk();
        $this->postJson($sendCodeUrl, ['email' => $attendee->email])->assertOk();

        $codes = [];
        Mail::assertSent(OnlineSessionVerificationCode::class, function (OnlineSessionVerificationCode $mail) use (&$codes, $attendee) {
            if ($mail->hasTo($attendee->email)) {
                $codes[] = $mail->code;
            }

            return true;
        }, 2);
        $this->assertCount(2, $codes);
        $this->assertNotSame($codes[0], $codes[1]);

        $joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]);
        $this->postJson($joinUrl, [
            'email' => $attendee->email,
            'verification_code' => $codes[0],
        ])->assertUnprocessable();
        $this->postJson($joinUrl, [
            'email' => $attendee->email,
            'verification_code' => $codes[1],
        ])->assertOk();
    }

    private function requestEmailVerificationCode(Meeting $meeting, Attendee $attendee): string
    {
        $this->postJson(URL::signedRoute('sessions.participants.send-code', ['meeting' => $meeting]), [
            'email' => $attendee->email,
        ])->assertOk();

        $code = null;
        Mail::assertSent(OnlineSessionVerificationCode::class, function (OnlineSessionVerificationCode $mail) use ($attendee, &$code) {
            if ($mail->hasTo($attendee->email)) {
                $code = $mail->code;

                $body = $mail->render();

                return str_contains($body, $mail->code)
                    && str_contains($body, 'expires in 10 minutes');
            }

            return false;
        });

        $this->assertNotNull($code);

        return $code;
    }
}
