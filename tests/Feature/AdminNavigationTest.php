<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\MeetingRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sidebar_options_open_their_pages(): void
    {
        $this->actingAs(User::factory()->create());

        $pages = [
            'users.index' => 'Users',
            'reports.index' => 'Reports & Analytics',
            'notifications.index' => 'Notifications',
            'settings.index' => 'Settings',
        ];

        foreach ($pages as $route => $label) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee($label);
        }
    }

    public function test_expenses_page_is_not_linked_from_the_admin_sidebar(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('meetings.index'))
            ->assertOk()
            ->assertDontSee('href="'.route('expenses.index').'"', false);
    }

    public function test_online_sessions_are_accessed_from_the_meetings_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('meetings.index'))
            ->assertOk()
            ->assertSee('Online sessions')
            ->assertSee('href="'.route('sessions.index').'"', false)
            ->assertDontSee('<span class="sb-label">Online Sessions</span>', false);

        $this->get(route('sessions.index'))
            ->assertOk()
            ->assertSee('Video meetings');
    }

    public function test_online_session_invite_link_is_available_to_hosts_and_guest_page_joins_directly(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-INVITE1',
            'name' => 'Alex Host',
            'email' => 'alex@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Open invite session',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
            'is_online' => true,
            'session_provider' => 'internal',
        ]);
        $inviteUrl = URL::signedRoute('sessions.join', ['meeting' => $meeting]);

        $this->actingAs(User::factory()->create())
            ->get(route('sessions.index'))
            ->assertOk()
            ->assertSee($inviteUrl)
            ->assertSee('Copy invite');

        auth()->logout();

        $this->get($inviteUrl)
            ->assertOk()
            ->assertSee('Open invite session')
            ->assertSee('Joining the meeting')
            ->assertSee('data-direct-join=', false)
            ->assertSee('data-is-host="false"', false)
            ->assertDontSee($inviteUrl)
            ->assertDontSee('Copy invite link');
    }

    public function test_invite_link_does_not_enable_offline_meetings(): void
    {
        $meeting = Meeting::create([
            'meeting_code' => 'AYK-OFFLINE',
            'name' => 'Alex Host',
            'email' => 'alex@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'In-person only',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
            'is_online' => false,
        ]);

        $this->get(route('sessions.join', $meeting))->assertForbidden();
        $this->get(URL::signedRoute('sessions.join', ['meeting' => $meeting]))->assertNotFound();
        $this->assertFalse($meeting->fresh()->is_online);
    }

    public function test_new_meeting_prompts_for_online_in_person_or_hybrid_type(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('meetings.index'))
            ->assertOk()
            ->assertSee('Choose meeting type')
            ->assertSee('Online meeting')
            ->assertSee('Multi company')
            ->assertSee('In-person meeting')
            ->assertSee('Hybrid meeting')
            ->assertSee("selectMeetingType('online_single')", false)
            ->assertSee("selectMeetingType('online_multi')", false)
            ->assertSee("selectMeetingType('in_person')", false)
            ->assertSee("selectMeetingType('hybrid')", false);
    }

    public function test_meeting_type_creates_online_in_person_and_hybrid_meetings(): void
    {
        $this->actingAs(User::factory()->create());
        $room = MeetingRoom::create(['name' => 'The Studio', 'number' => 'A-101', 'floor' => '1', 'location' => '24 Market Street']);
        $start = now()->addDays(2)->startOfHour();
        $details = [
            'name' => 'Alex Host',
            'email' => 'alex@example.test',
            'phone' => '5550100',
            'meeting_topic' => 'Online planning',
            'start_date' => $start->format('Y-m-d H:i:s'),
            'end_date' => $start->copy()->addHour()->format('Y-m-d H:i:s'),
        ];

        $this->post(route('meetings.store'), $details + ['meeting_type' => 'online'])
            ->assertRedirect(route('meetings.details', Meeting::where('meeting_topic', 'Online planning')->firstOrFail()));

        $online = Meeting::where('meeting_topic', 'Online planning')->firstOrFail();
        $this->assertTrue($online->is_online);
        $this->assertNull($online->meeting_room_id);
        $this->assertSame('internal', $online->session_provider);

        $this->post(route('meetings.store'), array_merge($details, [
            'meeting_type' => 'in_person',
            'meeting_topic' => 'Room required',
        ]))->assertSessionHasErrors('meeting_room_id');

        $inPersonStart = $start->copy()->addDays(1);
        $this->post(route('meetings.store'), array_merge($details, [
            'meeting_type' => 'in_person',
            'meeting_topic' => 'In-person planning',
            'start_date' => $inPersonStart->format('Y-m-d H:i:s'),
            'end_date' => $inPersonStart->copy()->addHour()->format('Y-m-d H:i:s'),
            'meeting_room_id' => $room->id,
        ]))->assertRedirect(route('meetings.details', Meeting::where('meeting_topic', 'In-person planning')->firstOrFail()));

        $inPerson = Meeting::where('meeting_topic', 'In-person planning')->firstOrFail();
        $this->assertFalse($inPerson->is_online);
        $this->assertSame($room->id, $inPerson->meeting_room_id);

        $hybridStart = $start->copy()->addDays(2);
        $hybridCreation = $this->post(route('meetings.store'), array_merge($details, [
            'meeting_type' => 'hybrid',
            'meeting_topic' => 'Hybrid planning',
            'start_date' => $hybridStart->format('Y-m-d H:i:s'),
            'end_date' => $hybridStart->copy()->addHour()->format('Y-m-d H:i:s'),
            'meeting_room_id' => $room->id,
            'description' => 'Discuss the quarterly roadmap.',
            'topic_category' => 'Planning',
        ]))->assertRedirect(route('meetings.details', Meeting::where('meeting_topic', 'Hybrid planning')->firstOrFail()));

        $hybrid = Meeting::where('meeting_topic', 'Hybrid planning')->firstOrFail();
        $this->assertTrue($hybrid->is_online);
        $this->assertSame($room->id, $hybrid->meeting_room_id);
        $this->assertSame('internal', $hybrid->session_provider);
        $this->assertNull($hybrid->physical_location);
        $this->assertSame('24 Market Street', $hybrid->room->location);
        $this->assertSame('Discuss the quarterly roadmap.', $hybrid->description);
        $this->assertSame('Planning', $hybrid->topic_category);
        $shareUrl = route('meetings.show', $hybrid->meeting_code);
        $hybridCreation->assertSessionHas('hybrid_share_url', $shareUrl);
        $this->get(route('meetings.show', $hybrid->meeting_code))
            ->assertOk()
            ->assertSee('Share this hybrid meeting with participants')
            ->assertSee('Copy meeting link')
            ->assertSee('value="'.$shareUrl.'"', false);
        $this->assertDatabaseHas('meetings', ['id' => $hybrid->id, 'session_url' => URL::signedRoute('sessions.join', ['meeting' => $hybrid])]);

        $nonAdminHost = User::factory()->nonAdmin()->create();
        $hybrid->update(['host_user_id' => $nonAdminHost->id]);
        $this->actingAs($nonAdminHost)->withSession(['hybrid_share_url' => $shareUrl])->get(route('meetings.show', $hybrid->meeting_code))
            ->assertOk()
            ->assertDontSee('Share this hybrid meeting with participants')
            ->assertDontSee('Online Participants');
        $this->getJson(route('meetings.hybrid-attendance', $hybrid))->assertForbidden();

        $this->actingAs(User::factory()->create())->get(route('meetings.show', $hybrid->meeting_code))
            ->assertOk()
            ->assertSee('Online Participants');

        auth()->logout();
        $this->get(route('meetings.show', $hybrid->meeting_code))
            ->assertOk()
            ->assertDontSee('Share this hybrid meeting with participants')
            ->assertDontSee('Copy meeting link');
    }

    public function test_hybrid_meeting_requires_a_room_selection_for_its_physical_location(): void
    {
        $host = User::factory()->create();

        $this->actingAs($host)->post(route('meetings.store'), [
            'meeting_type' => 'hybrid',
            'name' => $host->name,
            'email' => $host->email,
            'phone' => '555-0100',
            'meeting_topic' => 'Community session',
            'start_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(2)->addHour()->format('Y-m-d H:i:s'),
        ])->assertSessionHasErrors('meeting_room_id');

        $this->assertDatabaseMissing('meetings', ['meeting_topic' => 'Community session']);
    }

    public function test_hybrid_creation_form_matches_the_hybrid_meeting_flow(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('meetings.index'))
            ->assertOk()
            ->assertSee('Hybrid Session Details')
            ->assertSee('Type your own topic')
            ->assertSee('Participant Attendance')
            ->assertSee('Online Participants')
            ->assertSee('In-Person Participants')
            ->assertSee('Not Responded')
            ->assertSee('Unlimited participants (no fixed limit)')
            ->assertSee('Meeting Equipment & Technology')
            ->assertSee('Hybrid Meeting Information')
            ->assertSee('Secure AykonMeet video call')
            ->assertDontSee('Available after creation')
            ->assertSee('Physical Location / Meeting Room')
            ->assertDontSee('id="hybrid-location-input"', false);
    }

    public function test_sidebar_has_no_disabled_coming_soon_options_and_schedule_dialog_opens(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('meetings.index'))
            ->assertOk()
            ->assertSee('New meeting')
            ->assertDontSee('+ Add room')
            ->assertDontSee('Your rooms');
    }

    public function test_meetings_dashboard_shows_live_operations_timeline_arrivals_and_quick_panels(): void
    {
        $this->actingAs(User::factory()->create());
        $room = MeetingRoom::create(['name' => 'Room A', 'number' => 'A-1', 'floor' => '1']);
        $now = now();

        $liveMeeting = Meeting::create([
            'name' => 'Alex Host',
            'email' => 'alex@example.test',
            'phone' => '555-0100',
            'meeting_topic' => "Today's live planning",
            'start_date' => $now->copy()->subMinutes(30),
            'end_date' => $now->copy()->addMinutes(30),
            'meeting_room_id' => $room->id,
            'status' => 'in_progress',
            'started_at' => $now->copy()->subMinutes(30),
        ]);
        Attendee::create([
            'meeting_id' => $liveMeeting->id,
            'full_name' => 'Kofi Guest',
            'email' => 'kofi@example.test',
            'phone' => '555-0101',
            'checked_in_at' => $now->copy()->subMinutes(2),
        ]);
        $lateMeeting = Meeting::create([
            'name' => 'Alex Host',
            'email' => 'alex@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Late arrival meeting',
            'start_date' => $now->copy()->subMinutes(10),
            'end_date' => $now->copy()->addMinutes(40),
            'status' => 'in_progress',
        ]);
        Attendee::create([
            'meeting_id' => $lateMeeting->id,
            'full_name' => 'Late Guest',
            'email' => 'late@example.test',
            'phone' => '555-0102',
        ]);
        $noShowMeeting = Meeting::create([
            'name' => 'Alex Host',
            'email' => 'alex@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Missed meeting',
            'start_date' => $now->copy()->subHours(2),
            'end_date' => $now->copy()->subHour(),
            'status' => 'completed',
        ]);
        Attendee::create([
            'meeting_id' => $noShowMeeting->id,
            'full_name' => 'Absent Guest',
            'email' => 'absent@example.test',
            'phone' => '555-0103',
        ]);
        Meeting::create([
            'name' => 'Next Host',
            'email' => 'next@example.test',
            'phone' => '555-0104',
            'meeting_topic' => 'Upcoming in two hours',
            'start_date' => $now->copy()->addHours(2),
            'end_date' => $now->copy()->addHours(3),
            'status' => 'scheduled',
        ]);

        $this->get(route('meetings.index'))
            ->assertOk()
            ->assertSee("Today's Meetings Timeline", false)
            ->assertSee('Room Occupancy')
            ->assertSee('Room A')
            ->assertDontSee('Checked-in Attendees')
            ->assertDontSee('Kofi Guest')
            ->assertSee('href="'.route('meetings.check-ins').'"', false)
            ->assertDontSee('check-in-overview')
            ->assertDontSee('Late Guest')
            ->assertDontSee('Absent Guest')
            ->assertSee('Upcoming Meetings')
            ->assertSee('Upcoming in two hours')
            ->assertSee('Rooms Needing Attention')
            ->assertSee('System Health')
            ->assertSee('Camera permission and device health');

        $this->get(route('meetings.check-ins'))
            ->assertOk()
            ->assertSee('Check-in & arrivals')
            ->assertSee('Kofi Guest')
            ->assertSee('Late Guest')
            ->assertSee('Absent Guest')
            ->assertSee('No-show')
            ->assertSee('href="'.route('meetings.index').'"', false);
    }

    public function test_live_meetings_card_is_clickable_even_when_no_meeting_is_in_progress(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('meetings.index'))
            ->assertOk()
            ->assertDontSee('View live meetings')
            ->assertSee('No meetings in progress')
            ->assertSee('href="'.route('meetings.live').'"', false)
            ->assertSee('aria-label="View 0 live meetings"', false);

        Meeting::create([
            'name' => 'Live Host',
            'email' => 'live@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Currently in progress',
            'start_date' => now()->subMinutes(10),
            'end_date' => now()->addMinutes(20),
            'status' => 'in_progress',
        ]);

        $this->get(route('meetings.index'))
            ->assertOk()
            ->assertSee('View live meetings')
            ->assertSee('href="'.route('meetings.live').'"', false);

        $this->get(route('meetings.live'))
            ->assertOk()
            ->assertSee('Live meetings')
            ->assertSee('Currently in progress')
            ->assertSee('Open meeting');
    }

    public function test_total_meetings_card_opens_full_meeting_list(): void
    {
        $this->actingAs(User::factory()->create());
        $meeting = Meeting::create([
            'name' => 'Archive Host',
            'email' => 'archive@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Archived planning meeting',
            'start_date' => now()->subDays(2),
            'end_date' => now()->subDays(2)->addHour(),
            'status' => 'completed',
            'ended_at' => now()->subDays(2)->addHour(),
        ]);

        $this->get(route('meetings.index'))
            ->assertOk()
            ->assertSee('href="'.route('meetings.all').'"', false)
            ->assertDontSee('schedule-dialog');

        $this->get(route('meetings.all'))
            ->assertOk()
            ->assertSee('All meetings')
            ->assertSee('href="'.route('meetings.index').'#new-meeting"', false)
            ->assertSee('Archived planning meeting')
            ->assertSee($meeting->meeting_code)
            ->assertSee('completed')
            ->assertSee('action-attendance', false)
            ->assertSee('action-edit', false)
            ->assertSee('action-reschedule', false)
            ->assertSee('Search by topic, host, room, or code')
            ->assertSee('Filter by meeting status');
    }

    public function test_today_card_opens_a_list_filtered_to_the_current_day(): void
    {
        $this->actingAs(User::factory()->create());
        $today = now()->startOfDay();

        Meeting::create([
            'name' => 'Today Host',
            'email' => 'today@example.test',
            'phone' => '555-0101',
            'meeting_topic' => 'Today planning meeting',
            'start_date' => $today->copy()->addHours(10),
            'end_date' => $today->copy()->addHours(11),
            'status' => 'scheduled',
        ]);
        Meeting::create([
            'name' => 'Yesterday Host',
            'email' => 'yesterday@example.test',
            'phone' => '555-0102',
            'meeting_topic' => 'Yesterday planning meeting',
            'start_date' => $today->copy()->subDay()->addHours(10),
            'end_date' => $today->copy()->subDay()->addHours(11),
            'status' => 'completed',
        ]);

        $this->get(route('meetings.index'))
            ->assertOk()
            ->assertSee('href="'.route('meetings.all', ['date' => now()->toDateString()]).'"', false);

        $this->get(route('meetings.all', ['date' => now()->toDateString()]))
            ->assertOk()
            ->assertSee("Today's meetings")
            ->assertSee('Today planning meeting')
            ->assertDontSee('Yesterday planning meeting');
    }

    public function test_rooms_dashboard_owns_room_creation_and_available_rooms(): void
    {
        $this->actingAs(User::factory()->create());
        $room = MeetingRoom::create(['name' => 'The Studio', 'number' => 'A-101', 'floor' => '1']);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Available rooms')
            ->assertSee('The Studio')
            ->assertSee('A-101')
            ->assertSee('add-room-dialog')
            ->assertDontSee('New meeting');

        $this->post(route('meeting-rooms.store'), [
            'name' => 'Board Room',
            'number' => 'B-201',
            'floor' => '2',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertSee('Board Room')
            ->assertSee('The Studio');
    }

    public function test_reports_and_notifications_reflect_current_meeting_activity(): void
    {
        $this->actingAs(User::factory()->create());
        $start = now()->subMinutes(20);

        $meeting = Meeting::create([
            'name' => 'Alex Host',
            'email' => 'alex@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Quarterly planning',
            'start_date' => $start,
            'end_date' => $start->copy()->addHour(),
            'status' => 'in_progress',
            'started_at' => $start,
        ]);

        Attendee::create([
            'meeting_id' => $meeting->id,
            'full_name' => 'Taylor Guest',
            'email' => 'taylor@example.test',
            'phone' => '555-0101',
            'checked_in_at' => now(),
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('100%');

        $this->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Meeting in progress')
            ->assertSee('Quarterly planning');
    }
}
