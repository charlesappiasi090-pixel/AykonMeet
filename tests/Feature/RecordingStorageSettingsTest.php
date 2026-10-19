<?php

namespace Tests\Feature;

use App\Mail\OnlineSessionVerificationCode;
use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\OnlineSessionParticipant;
use App\Models\OnlineSessionRecording;
use App\Models\OnlineSessionSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RecordingStorageSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_recording_policy_user_access_and_all_saved_recordings(): void
    {
        $admin = User::factory()->create();
        $account = User::factory()->nonAdmin()->create();
        $meeting = $this->onlineMeeting($admin);
        $participant = OnlineSessionParticipant::create([
            'meeting_id' => $meeting->id,
            'peer_id' => 'd58fa5a8-97ef-4d41-a3aa-017af8dd6eac',
            'display_name' => 'Recording Host',
            'is_host' => true,
            'joined_at' => now(),
            'last_seen_at' => now(),
        ]);
        $recording = OnlineSessionRecording::create([
            'meeting_id' => $meeting->id,
            'participant_id' => $participant->id,
            'storage_path' => 'session-recordings/'.$meeting->id.'/meeting.webm',
            'file_size' => 2097152,
            'status' => 'ready',
            'started_at' => now(),
        ]);
        Storage::disk('local')->put($recording->storage_path, 'video');

        $this->actingAs($admin)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Recording & Storage')
            ->assertSee('All meeting recordings')
            ->assertSee($meeting->meeting_topic)
            ->assertSee('2.0 MB')
            ->assertSee(route('sessions.recordings.download', [$meeting, $recording]), false);

        $this->post(route('settings.recordings.update'), [
            'recording_enabled' => '1',
            'allow_attendee_recording' => '0',
            'downloads_for_hosts' => '1',
        ])->assertRedirect(route('settings.index'));

        $this->patch(route('settings.recording-users.update', $account), [
            'can_record_sessions' => '0',
        ])->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('online_session_settings', [
            'id' => 1,
            'recording_enabled' => true,
            'allow_attendee_recording' => false,
            'downloads_for_hosts' => true,
        ]);
        $this->assertFalse($account->fresh()->can_record_sessions);
    }

    public function test_recording_permissions_are_enforced_and_downloads_follow_admin_policy(): void
    {
        Storage::fake('local');
        $host = User::factory()->nonAdmin()->create(['can_record_sessions' => false]);
        $meeting = $this->onlineMeeting($host);
        $joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]);

        $hostJoin = $this->actingAs($host)
            ->postJson($joinUrl, ['email' => $host->email])
            ->assertOk()
            ->assertJsonPath('can_record', false)
            ->json();
        $recordingStartUrl = URL::signedRoute('sessions.recording.start', ['meeting' => $meeting]);
        $this->postJson($recordingStartUrl, [
            'peer_id' => $hostJoin['peer_id'],
            'token' => $hostJoin['token'],
        ])->assertForbidden();

        $host->update(['can_record_sessions' => true]);
        $hostJoin = $this->postJson($joinUrl, ['email' => $host->email])
            ->assertOk()
            ->assertJsonPath('can_record', true)
            ->json();
        $this->postJson($recordingStartUrl, [
            'peer_id' => $hostJoin['peer_id'],
            'token' => $hostJoin['token'],
        ])->assertCreated();

        $recording = $this->readyRecording($meeting, $hostJoin['peer_id']);
        $settings = OnlineSessionSetting::current();
        $settings->update(['downloads_for_hosts' => true]);
        $this->get(route('sessions.recordings.download', [$meeting, $recording]))->assertOk();
        $this->get(route('sessions.report', $meeting))
            ->assertOk()
            ->assertSee('Session recordings');
        $this->get(route('meetings.show', $meeting->meeting_code))
            ->assertOk()
            ->assertSee('Session report &amp; recordings', false);

        $otherUser = User::factory()->nonAdmin()->create();
        $this->actingAs($otherUser)
            ->get(route('sessions.recordings.download', [$meeting, $recording]))
            ->assertForbidden();

        $settings->update(['downloads_for_hosts' => false]);
        $this->actingAs($host)
            ->get(route('sessions.recordings.download', [$meeting, $recording]))
            ->assertForbidden();
        $this->get(route('sessions.report', $meeting))->assertForbidden();
        $this->actingAs(User::factory()->create())
            ->get(route('sessions.recordings.download', [$meeting, $recording]))
            ->assertOk();
    }

    public function test_attendee_recording_can_be_disabled_by_admin_policy(): void
    {
        Mail::fake();

        $host = User::factory()->create();
        $meeting = $this->onlineMeeting($host);
        $attendee = $meeting->attendees()->create([
            'full_name' => 'Invited attendee',
            'email' => 'invited@example.test',
            'phone' => '555-0101',
        ]);
        OnlineSessionSetting::current()->update(['allow_attendee_recording' => false]);

        $code = $this->requestEmailVerificationCode($meeting, $attendee);
        $joined = $this->postJson(URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]), [
            'email' => $attendee->email,
            'verification_code' => $code,
        ])->assertOk()
            ->assertJsonPath('can_record', false)
            ->json();

        $this->postJson(URL::signedRoute('sessions.recording.start', ['meeting' => $meeting]), [
            'peer_id' => $joined['peer_id'],
            'token' => $joined['token'],
        ])->assertForbidden();
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

                return true;
            }

            return false;
        });

        $this->assertNotNull($code);

        return $code;
    }

    private function onlineMeeting(User $host): Meeting
    {
        return Meeting::create([
            'meeting_code' => 'AYK-'.strtoupper(fake()->unique()->lexify('??????')),
            'name' => $host->name,
            'email' => $host->email,
            'phone' => '555-0100',
            'meeting_topic' => 'Recording policy session',
            'start_date' => now(),
            'end_date' => now()->addHour(),
            'status' => 'in_progress',
            'is_online' => true,
            'host_user_id' => $host->id,
            'session_provider' => 'internal',
        ]);
    }

    private function readyRecording(Meeting $meeting, string $peerId): OnlineSessionRecording
    {
        $participant = OnlineSessionParticipant::where('meeting_id', $meeting->id)
            ->where('peer_id', $peerId)
            ->firstOrFail();
        $path = 'session-recordings/'.$meeting->id.'/ready.webm';
        Storage::disk('local')->put($path, 'video');

        return OnlineSessionRecording::create([
            'meeting_id' => $meeting->id,
            'participant_id' => $participant->id,
            'storage_path' => $path,
            'file_size' => 5,
            'status' => 'ready',
            'started_at' => now(),
            'ended_at' => now(),
        ]);
    }
}
