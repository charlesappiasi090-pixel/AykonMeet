<?php

namespace Tests\Feature;

use App\Mail\OnlineSessionVerificationCode;
use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\OnlineSessionParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class HostPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_waiting_room_gates_a_guest_until_the_host_admits_them(): void
    {
        Mail::fake();

        $host = User::factory()->create();
        $meeting = $this->meeting([
            'host_user_id' => $host->id,
            'settings' => ['waiting_room' => true, 'waiting_room_message' => 'Almost ready!'],
        ]);
        $attendee = $this->attendee($meeting, 'Waiting Guest', 'waiting@example.test');

        $joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]);
        $heartbeatUrl = URL::signedRoute('sessions.participants.heartbeat', ['meeting' => $meeting]);
        $statusUrl = URL::signedRoute('sessions.participants.status', ['meeting' => $meeting]);
        $waitingRoomUrl = URL::signedRoute('sessions.participants.waiting-room', ['meeting' => $meeting]);
        $admitUrl = URL::signedRoute('sessions.participants.admit', ['meeting' => $meeting]);

        $guest = $this->postJson($joinUrl, [
            'email' => $attendee->email,
            'verification_code' => $this->code($meeting, $attendee),
        ])->assertOk()->json();

        $this->assertTrue($guest['waiting']);
        $this->assertSame('Almost ready!', $guest['waiting_room_message']);
        $this->assertSame([], $guest['participants']);
        $this->assertDatabaseHas('online_session_participants', [
            'peer_id' => $guest['peer_id'],
            'status' => OnlineSessionParticipant::STATUS_WAITING,
        ]);

        $guestAuth = ['peer_id' => $guest['peer_id'], 'token' => $guest['token']];

        $this->postJson($heartbeatUrl, $guestAuth)->assertOk()->assertJsonPath('status', 'waiting');
        $this->postJson($statusUrl, $guestAuth)->assertOk()->assertJsonPath('status', 'waiting');

        $this->postJson($waitingRoomUrl, $guestAuth)->assertForbidden();

        $this->actingAs($host)->postJson($joinUrl, ['email' => $host->email])->assertOk();

        $roster = $this->actingAs($host)->postJson($waitingRoomUrl, [])->assertOk()->json();
        $this->assertCount(1, $roster['waiting']);
        $this->assertSame($guest['peer_id'], $roster['waiting'][0]['peer_id']);
        $this->assertSame('Waiting Guest', $roster['waiting'][0]['display_name']);

        $this->actingAs($host)->postJson($admitUrl, ['peer_id' => $guest['peer_id']])->assertOk();
        $this->assertDatabaseHas('online_session_participants', [
            'peer_id' => $guest['peer_id'],
            'status' => OnlineSessionParticipant::STATUS_ADMITTED,
        ]);

        $admitted = $this->postJson($statusUrl, $guestAuth)->assertOk()->json();
        $this->assertSame($guest['peer_id'], $admitted['peer_id']);
        $this->assertFalse($admitted['is_co_host']);
    }

    public function test_share_link_guest_appears_in_the_waiting_room_under_the_name_they_enter(): void
    {
        $host = User::factory()->create();
        $meeting = $this->meeting([
            'host_user_id' => $host->id,
            'settings' => ['waiting_room' => true],
        ]);
        $joinPage = URL::signedRoute('sessions.join', ['meeting' => $meeting]);

        $this->get($joinPage)
            ->assertOk()
            ->assertSee('Enter your name so the host can see who is waiting to join.', false)
            ->assertSee('guestDisplayName', false);
        $this->assertDatabaseMissing('online_session_participants', ['meeting_id' => $meeting->id]);

        $this->postJson(URL::signedRoute('sessions.participants.guest-join', ['meeting' => $meeting]), [
            'display_name' => '   ',
        ])->assertStatus(422);

        $guest = $this->postJson(URL::signedRoute('sessions.participants.guest-join', ['meeting' => $meeting]), [
            'display_name' => '  Ada Lovelace  ',
        ])->assertOk()->json();

        $this->assertTrue($guest['waiting']);
        $this->assertDatabaseHas('online_session_participants', [
            'peer_id' => $guest['peer_id'],
            'display_name' => 'Ada Lovelace',
            'status' => OnlineSessionParticipant::STATUS_WAITING,
        ]);

        $this->actingAs($host)->postJson(URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]), [
            'email' => $host->email,
        ])->assertOk();

        $this->actingAs($host)
            ->postJson(URL::signedRoute('sessions.participants.waiting-room', ['meeting' => $meeting]), [])
            ->assertOk()
            ->assertJsonPath('waiting.0.display_name', 'Ada Lovelace');
    }

    public function test_host_can_admit_everyone_from_the_waiting_room(): void
    {
        Mail::fake();

        $host = User::factory()->create();
        $meeting = $this->meeting([
            'host_user_id' => $host->id,
            'settings' => ['waiting_room' => true],
        ]);
        $joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]);
        $admitAllUrl = URL::signedRoute('sessions.participants.admit-all', ['meeting' => $meeting]);

        foreach (['One Guest', 'Two Guest'] as $index => $name) {
            $attendee = $this->attendee($meeting, $name, 'guest'.$index.'@example.test');
            $this->postJson($joinUrl, [
                'email' => $attendee->email,
                'verification_code' => $this->code($meeting, $attendee),
            ])->assertOk();
        }

        $this->actingAs($host)->postJson($joinUrl, ['email' => $host->email])->assertOk();

        $this->actingAs($host)
            ->postJson($admitAllUrl, [])
            ->assertOk()
            ->assertJsonPath('admitted', 2);

        $this->assertSame(2, OnlineSessionParticipant::where('meeting_id', $meeting->id)
            ->where('status', OnlineSessionParticipant::STATUS_ADMITTED)
            ->where('is_host', false)
            ->count());
    }

    public function test_a_pre_designated_co_host_skips_the_waiting_room_with_delegated_permissions(): void
    {
        Mail::fake();

        $host = User::factory()->create();
        $meeting = $this->meeting([
            'host_user_id' => $host->id,
            'settings' => [
                'waiting_room' => true,
                'co_host' => 'Dana CoHost',
                'co_host_permissions' => ['mute_participants', 'manage_waiting_room'],
            ],
        ]);
        $attendee = $this->attendee($meeting, 'Dana CoHost', 'dana@example.test');
        $joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]);
        $waitingRoomUrl = URL::signedRoute('sessions.participants.waiting-room', ['meeting' => $meeting]);

        $join = $this->postJson($joinUrl, [
            'email' => $attendee->email,
            'verification_code' => $this->code($meeting, $attendee),
        ])->assertOk()->json();

        $this->assertFalse($join['waiting']);
        $this->assertTrue($join['is_co_host']);
        $this->assertTrue($join['permissions']['mute_participants']);
        $this->assertTrue($join['permissions']['manage_waiting_room']);
        $this->assertFalse($join['permissions']['remove_participants']);
        $this->assertFalse($join['permissions']['promote_co_hosts']);

        $this->postJson($waitingRoomUrl, [
            'actor_peer_id' => $join['peer_id'],
            'actor_token' => $join['token'],
        ])->assertOk();
    }

    public function test_host_can_promote_a_co_host_and_their_delegated_capabilities_are_enforced(): void
    {
        Mail::fake();

        $host = User::factory()->create();
        $meeting = $this->meeting([
            'host_user_id' => $host->id,
            'settings' => ['waiting_room' => false],
        ]);
        $joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]);
        $muteUrl = URL::signedRoute('sessions.participants.mute', ['meeting' => $meeting]);
        $removeUrl = URL::signedRoute('sessions.participants.remove', ['meeting' => $meeting]);
        $coHostUrl = URL::signedRoute('sessions.participants.co-host', ['meeting' => $meeting]);
        $waitingRoomUrl = URL::signedRoute('sessions.participants.waiting-room', ['meeting' => $meeting]);
        $heartbeatUrl = URL::signedRoute('sessions.participants.heartbeat', ['meeting' => $meeting]);

        $this->actingAs($host)->postJson($joinUrl, ['email' => $host->email])->assertOk();

        $promoted = $this->guest($meeting, 'Promoted Guest', 'promoted@example.test');
        $target = $this->guest($meeting, 'Target Guest', 'target@example.test');

        $this->actingAs($host)
            ->postJson($coHostUrl, [
                'peer_id' => $promoted['peer_id'],
                'is_co_host' => true,
                'permissions' => ['mute_participants'],
            ])
            ->assertOk();
        $this->assertDatabaseHas('online_session_participants', [
            'peer_id' => $promoted['peer_id'],
            'is_co_host' => true,
        ]);

        $actor = ['actor_peer_id' => $promoted['peer_id'], 'actor_token' => $promoted['token']];

        auth()->logout();

        $this->postJson($muteUrl, $actor + ['peer_id' => $target['peer_id'], 'muted' => true])
            ->assertOk();
        $this->assertTrue(OnlineSessionParticipant::where('peer_id', $target['peer_id'])->value('is_muted_by_host'));

        $this->postJson($coHostUrl, $actor + ['peer_id' => $target['peer_id'], 'is_co_host' => true])
            ->assertForbidden();
        $this->postJson($removeUrl, $actor + ['peer_id' => $target['peer_id']])
            ->assertForbidden();
        $this->postJson($waitingRoomUrl, $actor)->assertForbidden();

        $this->postJson($removeUrl, [
            'peer_id' => $target['peer_id'],
            'actor_peer_id' => $promoted['peer_id'],
            'actor_token' => str_repeat('x', 64),
        ])->assertForbidden();

        $this->actingAs($host)->postJson($removeUrl, ['peer_id' => $target['peer_id']])->assertOk();

        $this->postJson($heartbeatUrl, ['peer_id' => $target['peer_id'], 'token' => $target['token']])
            ->assertStatus(403)
            ->assertJsonPath('status', 'removed');
    }

    public function test_participant_permission_flags_block_chat_hands_and_screen_share(): void
    {
        Mail::fake();

        $host = User::factory()->create();
        $meeting = $this->meeting([
            'host_user_id' => $host->id,
            'settings' => [
                'waiting_room' => false,
                'allow_chat' => false,
                'allow_participant_hand' => false,
                'allow_screen_share' => false,
            ],
        ]);
        $joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]);
        $chatUrl = URL::signedRoute('sessions.chat.send', ['meeting' => $meeting]);
        $handUrl = URL::signedRoute('sessions.hand', ['meeting' => $meeting]);
        $screenUrl = URL::signedRoute('sessions.screen-share', ['meeting' => $meeting]);

        $hostJoin = $this->actingAs($host)->postJson($joinUrl, ['email' => $host->email])->assertOk()->json();
        $guest = $this->guest($meeting, 'Muted Guest', 'muted@example.test');

        $guestAuth = ['peer_id' => $guest['peer_id'], 'token' => $guest['token']];

        $this->postJson($chatUrl, $guestAuth + ['message' => 'Blocked message'])->assertForbidden();
        $this->postJson($handUrl, $guestAuth + ['raised' => true])->assertForbidden();
        $this->postJson($screenUrl, $guestAuth)->assertForbidden();

        $hostAuth = ['peer_id' => $hostJoin['peer_id'], 'token' => $hostJoin['token']];
        $this->postJson($chatUrl, $hostAuth + ['message' => 'Host message'])->assertCreated();
        $this->postJson($screenUrl, $hostAuth)->assertOk();
    }

    public function test_meeting_settings_persist_host_control_options_and_filter_co_host_permissions(): void
    {
        $admin = User::factory()->create();
        $meeting = $this->meeting();

        $this->actingAs($admin)
            ->post(route('meetings.settings.update', $meeting), [
                'waiting_room' => '1',
                'waiting_room_message' => 'The host will be right with you.',
                'mute_on_entry' => '1',
                'allow_participant_hand' => '1',
                'co_host' => 'Trusted Person',
                'co_host_permissions' => ['mute_participants', 'remove_participants'],
            ])
            ->assertSessionHas('success');

        $settings = $meeting->fresh()->sessionSettings();

        $this->assertTrue($settings['waiting_room']);
        $this->assertSame('The host will be right with you.', $settings['waiting_room_message']);
        $this->assertTrue($settings['mute_on_entry']);
        $this->assertTrue($settings['allow_participant_hand']);
        $this->assertSame('Trusted Person', $settings['co_host']);
        $this->assertSame(['mute_participants', 'remove_participants'], $settings['co_host_permissions']);
    }

    public function test_rejoining_replaces_the_previous_session_instead_of_duplicating_the_person(): void
    {
        Mail::fake();

        $host = User::factory()->create();
        $meeting = $this->meeting(['host_user_id' => $host->id]);
        $attendee = $this->attendee($meeting, 'Charles', 'charles@example.test');
        $joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]);
        $guestUrl = URL::signedRoute('sessions.participants.guest-join', ['meeting' => $meeting]);

        $first = $this->postJson($joinUrl, [
            'email' => $attendee->email,
            'verification_code' => $this->code($meeting, $attendee),
        ])->assertOk()->json();
        $second = $this->postJson($joinUrl, [
            'email' => $attendee->email,
            'verification_code' => $this->code($meeting, $attendee),
        ])->assertOk()->json();

        $this->assertNotSame($first['peer_id'], $second['peer_id']);
        $this->assertNotNull(OnlineSessionParticipant::where('peer_id', $first['peer_id'])->value('left_at'));
        $this->assertNull(OnlineSessionParticipant::where('peer_id', $second['peer_id'])->value('left_at'));
        $this->assertNull($attendee->refresh()->checked_out_at);
        $this->assertSame([], $second['participants']);

        $this->postJson($guestUrl, ['display_name' => 'Rayyy'])->assertOk();
        $returningGuest = $this->postJson($guestUrl, ['display_name' => 'Rayyy'])->assertOk()->json();
        $otherGuest = $this->postJson($guestUrl, ['display_name' => 'Ada'])->assertOk()->json();

        $roster = $this->postJson(URL::signedRoute('sessions.participants.heartbeat', ['meeting' => $meeting]), [
            'peer_id' => $otherGuest['peer_id'],
            'token' => $otherGuest['token'],
        ])->assertOk()->json();

        $names = collect($roster['participants'])->pluck('display_name')->sort()->values()->all();
        $this->assertSame(['Charles', 'Rayyy'], $names);
        $this->assertSame(1, collect($roster['participants'])->where('display_name', 'Rayyy')->count());
        $this->assertSame($returningGuest['peer_id'], collect($roster['participants'])->firstWhere('display_name', 'Rayyy')['peer_id']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function meeting(array $overrides = []): Meeting
    {
        return Meeting::create(array_merge([
            'meeting_code' => 'AYK-PERM'.random_int(1000, 9999),
            'name' => 'Meeting host',
            'email' => 'host@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Host permissions session',
            'start_date' => now(),
            'end_date' => now()->addHour(),
            'status' => 'in_progress',
            'is_online' => true,
        ], $overrides));
    }

    private function attendee(Meeting $meeting, string $name, string $email): Attendee
    {
        return $meeting->attendees()->create([
            'full_name' => $name,
            'email' => $email,
            'phone' => '555-01'.random_int(10, 99),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function guest(Meeting $meeting, string $name, string $email): array
    {
        $attendee = $this->attendee($meeting, $name, $email);

        return $this->actingAs(User::factory()->create())
            ->postJson(URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]), [
                'email' => $attendee->email,
                'verification_code' => $this->code($meeting, $attendee),
            ])->assertOk()->json();
    }

    private function code(Meeting $meeting, Attendee $attendee): string
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
}
