<?php

namespace Tests\Feature;

use App\Mail\AttendeeInvitation;
use App\Models\Meeting;
use App\Models\MeetingCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MeetingDetailsDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function meeting(array $attributes = []): Meeting
    {
        return Meeting::create(array_merge([
            'meeting_code' => 'AYK-DETAIL1',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '123456',
            'meeting_topic' => 'Quarterly review',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
            'meeting_type' => 'online',
            'is_online' => true,
        ], $attributes));
    }

    public function test_creating_a_meeting_redirects_to_the_details_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('meetings.store'), [
                'meeting_type' => 'online',
                'is_multi_company' => '1',
                'company_name' => 'Acme Ltd',
                'companies' => ['Contoso'],
                'name' => 'Alex Host',
                'email' => 'alex@example.test',
                'phone' => '5550100',
                'meeting_topic' => 'Kickoff call',
                'start_date' => now()->addDay()->format('Y-m-d H:i:s'),
                'end_date' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('meetings.details', Meeting::where('meeting_topic', 'Kickoff call')->firstOrFail()))
            ->assertSessionHas('meeting_created', true)
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'Meeting Created Successfully'));

        $meeting = Meeting::where('meeting_topic', 'Kickoff call')->firstOrFail();
        $this->get(route('meetings.details', $meeting))
            ->assertOk()
            ->assertSee('Meeting Created Successfully')
            ->assertSee('Kickoff call')
            ->assertSee('Participating Companies')
            ->assertSee('Acme Ltd')
            ->assertSee('Contoso')
            ->assertSee('Invitation Management')
            ->assertSee('Meeting Settings');
    }

    public function test_details_dashboard_renders_meeting_overview(): void
    {
        $meeting = $this->meeting();

        $this->actingAs(User::factory()->create())
            ->get(route('meetings.details', $meeting))
            ->assertOk()
            ->assertSee($meeting->meeting_code)
            ->assertSee('Quarterly review')
            ->assertSee('Expected Participants');
    }

    public function test_organizer_can_add_a_company_with_a_representative(): void
    {
        Mail::fake();
        $meeting = $this->meeting();

        $this->actingAs(User::factory()->create())
            ->post(route('meetings.companies.store', $meeting), [
                'name' => 'Acme Ltd',
                'rep_name' => 'Rep One',
                'rep_email' => 'rep@acme.test',
                'rep_phone' => '0712345678',
                'rep_department' => 'Operations',
            ])
            ->assertRedirect();

        $company = MeetingCompany::where('meeting_id', $meeting->id)->where('name', 'Acme Ltd')->firstOrFail();
        $this->assertNotNull($company->invited_at);

        $attendee = $meeting->attendees()->where('email', 'rep@acme.test')->firstOrFail();
        $this->assertSame($company->id, $attendee->meeting_company_id);
        $this->assertSame('Operations', $attendee->department);

        Mail::assertSent(AttendeeInvitation::class, fn ($mail) => $mail->hasTo('rep@acme.test'));
    }

    public function test_organizer_can_update_a_company_and_remove_it(): void
    {
        $meeting = $this->meeting();
        $company = MeetingCompany::forName($meeting, 'Acme Ltd');

        $this->actingAs(User::factory()->create())
            ->patch(route('meetings.companies.update', [$meeting, $company]), [
                'name' => 'Acme Holdings',
                'contact_name' => 'Dana',
                'contact_email' => 'dana@acme.test',
                'status' => 'accepted',
            ])
            ->assertRedirect();

        $company->refresh();
        $this->assertSame('Acme Holdings', $company->name);
        $this->assertSame('accepted', $company->status);

        $this->actingAs(User::factory()->create())
            ->delete(route('meetings.companies.destroy', [$meeting, $company]))
            ->assertRedirect();

        $this->assertDatabaseMissing('meeting_companies', ['id' => $company->id]);
    }

    public function test_organizer_can_save_meeting_settings(): void
    {
        $meeting = $this->meeting();

        $this->actingAs(User::factory()->create())
            ->post(route('meetings.settings.update', $meeting), [
                'waiting_room' => '1',
                'allow_chat' => '1',
                'default_camera_on' => '0',
                'password' => 'secret',
                'max_participants' => 25,
            ])
            ->assertRedirect();

        $settings = $meeting->refresh()->sessionSettings();
        $this->assertTrue($settings['waiting_room']);
        $this->assertTrue($settings['allow_chat']);
        $this->assertFalse($settings['default_camera_on']);
        $this->assertSame('secret', $settings['password']);
        $this->assertSame(25, (int) $settings['max_participants']);
    }

    public function test_company_search_returns_matching_names(): void
    {
        $meeting = $this->meeting();
        MeetingCompany::forName($meeting, 'Northwind Traders');
        MeetingCompany::forName($meeting, 'Contoso');

        $this->actingAs(User::factory()->create())
            ->getJson(route('meetings.companies.search', ['q' => 'North']))
            ->assertOk()
            ->assertJson(['companies' => ['Northwind Traders']]);
    }

    public function test_lobby_renders_device_check(): void
    {
        $meeting = $this->meeting();

        $this->actingAs(User::factory()->create())
            ->get(route('meetings.lobby', $meeting))
            ->assertOk()
            ->assertSee('Pre-meeting lobby')
            ->assertSee('Test your camera')
            ->assertSee('Enter Meeting Room');
    }

    public function test_non_admin_cannot_access_the_details_dashboard(): void
    {
        $meeting = $this->meeting();

        $this->actingAs(User::factory()->nonAdmin()->create())
            ->get(route('meetings.details', $meeting))
            ->assertForbidden();
    }
}
