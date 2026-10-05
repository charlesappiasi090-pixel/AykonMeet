<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\MeetingRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_starting_a_meeting_sets_started_time_and_status(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-TEST1',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Sprint planning',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
        ]);

        $user = \App\Models\User::factory()->create();

        $this->actingAs($user)
            ->post(route('meetings.start', $meeting))
            ->assertRedirect(route('meetings.show', $meeting->meeting_code));

        $meeting->refresh();

        $this->assertNotNull($meeting->started_at);
        $this->assertSame('in_progress', $meeting->status);
    }

    public function test_ending_a_meeting_sets_completed_status_and_end_time(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-TEST2',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Sprint retrospective',
            'start_date' => now()->subHour(),
            'end_date' => now()->addHour(),
            'status' => 'in_progress',
            'started_at' => now()->subMinutes(15),
        ]);

        $user = \App\Models\User::factory()->create();

        $this->actingAs($user)
            ->post(route('meetings.end', $meeting))
            ->assertRedirect();

        $meeting->refresh();

        $this->assertNotNull($meeting->ended_at);
        $this->assertSame('completed', $meeting->status);
    }

    public function test_completed_meeting_can_be_exported_as_pdf(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-PDF01',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'PDF summary',
            'start_date' => now()->subHours(2),
            'end_date' => now()->subHour(),
            'status' => 'completed',
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->get(route('meetings.pdf', $meeting))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_authenticated_admin_can_reschedule_a_meeting(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-RESCH1',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Reschedule demo',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
        ]);

        $newStart = now()->addDays(2)->setTime(10, 30);
        $newEnd = $newStart->copy()->addHour();

        $this->actingAs(\App\Models\User::factory()->create())
            ->patch(route('meetings.reschedule', $meeting), [
                'start_date' => $newStart->format('Y-m-d H:i'),
                'end_date' => $newEnd->format('Y-m-d H:i'),
            ])
            ->assertRedirect();

        $meeting->refresh();

        $this->assertTrue($meeting->start_date->equalTo($newStart));
        $this->assertTrue($meeting->end_date->equalTo($newEnd));
        $this->assertSame('AYK-RESCH1', $meeting->meeting_code);
    }

    public function test_new_meeting_cannot_overlap_an_existing_meeting(): void
    {
        Meeting::create([
            'meeting_code' => 'AYK-BOOKED1',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Existing meeting',
            'start_date' => '2026-09-25 10:00:00',
            'end_date' => '2026-09-25 11:00:00',
            'status' => 'scheduled',
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->post(route('meetings.store'), [
                'name' => 'John Smith',
                'email' => 'john@example.com',
                'phone' => '654321',
                'meeting_topic' => 'Overlapping meeting',
                'start_date' => '2026-09-25 10:30:00',
                'end_date' => '2026-09-25 11:30:00',
            ])
            ->assertSessionHasErrors('start_date');

        $this->assertDatabaseMissing('meetings', ['meeting_topic' => 'Overlapping meeting']);
    }

    public function test_overlapping_meetings_are_allowed_in_different_rooms(): void
    {
        $firstRoom = MeetingRoom::create(['name' => 'Room One', 'number' => 'A-101', 'floor' => '1']);
        $secondRoom = MeetingRoom::create(['name' => 'Room Two', 'number' => 'A-102', 'floor' => '1']);

        Meeting::create([
            'meeting_code' => 'AYK-ROOM1',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Room one meeting',
            'start_date' => '2026-09-25 10:00:00',
            'end_date' => '2026-09-25 11:00:00',
            'meeting_room_id' => $firstRoom->id,
            'status' => 'scheduled',
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->post(route('meetings.store'), [
                'name' => 'John Smith',
                'email' => 'john@example.com',
                'phone' => '654321',
                'meeting_topic' => 'Room two meeting',
                'start_date' => '2026-09-25 10:30:00',
                'end_date' => '2026-09-25 11:30:00',
                'meeting_room_id' => $secondRoom->id,
            ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('meetings', ['meeting_topic' => 'Room two meeting']);
    }

    public function test_back_to_back_meetings_are_allowed(): void
    {
        Meeting::create([
            'meeting_code' => 'AYK-BOOKED2',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'First meeting',
            'start_date' => '2026-09-26 10:00:00',
            'end_date' => '2026-09-26 11:00:00',
            'status' => 'scheduled',
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->post(route('meetings.store'), [
                'name' => 'John Smith',
                'email' => 'john@example.com',
                'phone' => '654321',
                'meeting_topic' => 'Next meeting',
                'start_date' => '2026-09-26 11:00:00',
                'end_date' => '2026-09-26 12:00:00',
            ])
            ->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('meetings', ['meeting_topic' => 'Next meeting']);
    }

    public function test_rescheduling_cannot_overlap_another_meeting(): void
    {
        $existingMeeting = Meeting::create([
            'meeting_code' => 'AYK-BOOKED3',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Existing slot',
            'start_date' => '2026-09-27 14:00:00',
            'end_date' => '2026-09-27 15:00:00',
            'status' => 'scheduled',
        ]);

        $meeting = Meeting::create([
            'meeting_code' => 'AYK-MOVE1',
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'phone' => '654321',
            'meeting_topic' => 'Movable meeting',
            'start_date' => '2026-09-27 16:00:00',
            'end_date' => '2026-09-27 17:00:00',
            'status' => 'scheduled',
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->patch(route('meetings.reschedule', $meeting), [
                'start_date' => $existingMeeting->start_date->format('Y-m-d H:i:s'),
                'end_date' => $existingMeeting->end_date->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHasErrors('start_date');

        $meeting->refresh();
        $this->assertSame('2026-09-27 16:00:00', $meeting->start_date->format('Y-m-d H:i:s'));
    }

    public function test_admin_console_requires_authentication(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_access_console(): void
    {
        $user = \App\Models\User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_booked_meetings_show_a_reschedule_action(): void
    {
        Meeting::create([
            'meeting_code' => 'AYK-BOOKED',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Booked meeting',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Reschedule')
            ->assertSee('Attendance')
            ->assertSee('Cancel')
            ->assertSee(route('meetings.reschedule.edit', 1), false);
    }

    public function test_attendance_page_has_upload_and_manual_check_in_without_start_controls(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-ATTEND1',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Attendance session',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->get(route('meetings.attendance', $meeting))
            ->assertOk()
            ->assertSee('Upload attendee list')
            ->assertSee('Add attendee manually')
            ->assertDontSee('Take attendance')
            ->assertDontSee('Start meeting');
    }

    public function test_first_admin_can_register_when_no_users_exist(): void
    {
        $this->assertDatabaseMissing('users', ['email' => 'admin@aykonmeet.test']);

        $this->post(route('register.submit'), [
            'name' => 'System Admin',
            'email' => 'admin@aykonmeet.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'admin@aykonmeet.test']);
    }

    public function test_root_route_serves_public_meeting_join_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Join your')
            ->assertSee('Meeting code');
    }

    public function test_guest_can_open_a_meeting_with_its_code(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-GUEST1',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Guest meeting',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
        ]);

        $this->get(route('meetings.show', $meeting->meeting_code))
            ->assertOk()
            ->assertSee('Guest meeting');
    }

    public function test_unknown_meeting_code_returns_to_join_page_with_an_error(): void
    {
        $this->get(route('meetings.show', 'AYK-MISSING'))
            ->assertRedirect(route('landing'))
            ->assertSessionHas('error', 'We could not find a meeting with that code. Check it and try again.');
    }

    public function test_admin_can_edit_meeting_details(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-EDIT01',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Original topic',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->patch(route('meetings.update', $meeting), [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'phone' => '123456',
                'meeting_topic' => 'Updated topic',
                'start_date' => now()->addHour()->format('Y-m-d H:i'),
                'end_date' => now()->addHours(2)->format('Y-m-d H:i'),
                'notes' => 'Today we agreed on the roadmap.',
            ])
            ->assertRedirect(route('meetings.attendance', $meeting));

        $meeting->refresh();

        $this->assertSame('Updated topic', $meeting->meeting_topic);
        $this->assertSame('Today we agreed on the roadmap.', $meeting->notes);
    }

    public function test_admin_can_edit_meeting_room_and_updates_conflict_checks(): void
    {
        $room = MeetingRoom::create(['name' => 'Boardroom', 'number' => 'B-2', 'floor' => '1']);

        $existing = Meeting::create([
            'meeting_code' => 'AYK-EDIT02',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Existing booking',
            'start_date' => '2026-10-01 10:00:00',
            'end_date' => '2026-10-01 11:00:00',
            'meeting_room_id' => $room->id,
            'status' => 'scheduled',
        ]);

        $meeting = Meeting::create([
            'meeting_code' => 'AYK-EDIT03',
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'phone' => '654321',
            'meeting_topic' => 'Movable meeting',
            'start_date' => '2026-10-01 12:00:00',
            'end_date' => '2026-10-01 13:00:00',
            'status' => 'scheduled',
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->patch(route('meetings.update', $meeting), [
                'name' => 'John Smith',
                'email' => 'john@example.com',
                'phone' => '654321',
                'meeting_topic' => 'Movable meeting',
                'start_date' => '2026-10-01 10:30:00',
                'end_date' => '2026-10-01 11:30:00',
                'meeting_room_id' => $room->id,
            ])
            ->assertSessionHasErrors('start_date');

        $this->assertSame('2026-10-01 12:00:00', $meeting->refresh()->start_date->format('Y-m-d H:i:s'));
    }

    public function test_admin_can_cancel_a_meeting_and_attendees_are_notified(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $meeting = Meeting::create([
            'meeting_code' => 'AYK-CANCEL1',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'To be cancelled',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
        ]);

        $meeting->attendees()->create(['full_name' => 'Alice Example', 'email' => 'alice@example.com', 'phone' => '111']);

        $this->actingAs(\App\Models\User::factory()->create())
            ->post(route('meetings.cancel', $meeting))
            ->assertRedirect();

        $meeting->refresh();

        $this->assertSame('cancelled', $meeting->status);
        $this->assertNotNull($meeting->cancelled_at);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\MeetingCancelled::class);
    }

    public function test_cancelled_meeting_cannot_be_started_or_exported(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-CANCEL2',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Cancelled session',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $user = \App\Models\User::factory()->create();

        $this->actingAs($user)
            ->post(route('meetings.start', $meeting))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->actingAs($user)
            ->get(route('meetings.pdf', $meeting))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_cancelled_meeting_can_be_reopened(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-REOPEN1',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Comeback meeting',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->post(route('meetings.reopen', $meeting))
            ->assertRedirect();

        $meeting->refresh();

        $this->assertSame('scheduled', $meeting->status);
        $this->assertNull($meeting->cancelled_at);
    }

    public function test_admin_can_save_meeting_notes(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-NOTES01',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Notes test',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->post(route('meetings.notes', $meeting), ['notes' => 'Decision: release on Friday.'])
            ->assertRedirect();

        $this->assertSame('Decision: release on Friday.', $meeting->refresh()->notes);
    }

    public function test_attendee_can_be_checked_in_checked_out_and_removed(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-ATT01',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Attendee workflow',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
        ]);

        $attendee = $meeting->attendees()->create(['full_name' => 'Alice Example', 'email' => 'alice@example.com', 'phone' => '111']);
        $user = \App\Models\User::factory()->create();

        $this->actingAs($user)
            ->post(route('kiosk.check-in', $meeting), ['attendee_code' => $attendee->attendee_code])
            ->assertRedirect();

        $this->assertNotNull($attendee->refresh()->checked_in_at);

        $this->actingAs($user)
            ->post(route('attendees.check-out', $attendee))
            ->assertRedirect();

        $this->assertNotNull($attendee->refresh()->checked_out_at);

        $this->actingAs($user)
            ->post(route('attendees.undo-check-in', $attendee))
            ->assertRedirect();

        $attendee->refresh();
        $this->assertNull($attendee->checked_in_at);
        $this->assertNull($attendee->checked_out_at);

        $this->actingAs($user)
            ->post(route('attendees.destroy', $attendee))
            ->assertRedirect();

        $this->assertDatabaseMissing('attendees', ['id' => $attendee->id]);
    }

    public function test_kiosk_self_check_in_is_public(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-KIOSK1',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Kiosk session',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
        ]);

        $attendee = $meeting->attendees()->create(['full_name' => 'Alice Example', 'email' => 'alice@example.com', 'phone' => '111']);

        $this->post(route('kiosk.check-in', $meeting), ['attendee_code' => $attendee->attendee_code])
            ->assertRedirect()
            ->assertSessionHas('kiosk_success');

        $this->assertNotNull($attendee->refresh()->checked_in_at);

        $this->get(route('kiosk.show', $meeting->meeting_code))
            ->assertOk()
            ->assertSee('SELF CHECK-IN')
            ->assertSee($meeting->meeting_code);
    }

    public function test_badges_page_renders_qr_codes_for_attendees(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-BADGE1',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Badge session',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
        ]);

        $attendee = $meeting->attendees()->create(['full_name' => 'Alice Example', 'email' => 'alice@example.com', 'phone' => '111']);

        $this->actingAs(\App\Models\User::factory()->create())
            ->get(route('meetings.badges', $meeting))
            ->assertOk()
            ->assertSee('Alice Example')
            ->assertSee('<svg', false);

        $this->assertSame('AYK-BADGE1', $meeting->meeting_code);
    }

    public function test_exported_pdf_includes_meeting_notes(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-PDFNOTE',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Notes in report',
            'start_date' => now()->subHours(2),
            'end_date' => now()->subHour(),
            'status' => 'completed',
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
            'notes' => 'Unanimous approval.',
        ]);

        $this->actingAs(\App\Models\User::factory()->create())
            ->get(route('meetings.pdf', $meeting))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
