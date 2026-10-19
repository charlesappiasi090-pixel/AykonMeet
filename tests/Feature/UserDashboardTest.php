<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\MeetingCompany;
use App\Models\User;
use App\Notifications\MeetingInvitationReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function meeting(): Meeting
    {
        return Meeting::create([
            'meeting_code' => 'AYK-DASH01',
            'name' => 'Jane Host',
            'email' => 'host@example.test',
            'phone' => '123456',
            'meeting_topic' => 'Partner sync',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDay()->addHour(),
            'status' => 'scheduled',
            'meeting_type' => 'online',
            'is_online' => true,
            'is_multi_company' => true,
        ]);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_registered_user_sees_invitations_and_notifications_then_they_are_marked_read(): void
    {
        $meeting = $this->meeting();
        $company = MeetingCompany::forName($meeting, 'Acme Ltd');
        $attendee = Attendee::create([
            'meeting_id' => $meeting->id,
            'meeting_company_id' => $company->id,
            'full_name' => 'Ravi Patel',
            'email' => 'ravi@acme.test',
            'phone' => '5550102',
            'invitation_status' => 'sent',
        ]);
        $user = User::factory()->nonAdmin()->create(['email' => 'ravi@acme.test']);
        $user->notify(new MeetingInvitationReceived($meeting, $attendee));

        $this->assertSame(1, $user->fresh()->unreadNotifications()->count());

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Partner sync')
            ->assertSee('New meeting invitation')
            ->assertSee('Jane Host')
            ->assertSee('Acme Ltd');

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_non_admin_is_redirected_to_the_dashboard_after_login(): void
    {
        $user = User::factory()->nonAdmin()->create();

        $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_admin_is_redirected_to_the_admin_console_after_login(): void
    {
        $admin = User::factory()->create();

        $this->post(route('login.submit'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));
    }
}
