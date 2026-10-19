<?php

namespace Tests\Feature;

use App\Mail\AttendeeInvitation;
use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\MeetingCompany;
use App\Models\OnlineSessionParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MeetingInvitationTest extends TestCase
{
    use RefreshDatabase;

    private function meeting(array $attributes = []): Meeting
    {
        return Meeting::create(array_merge([
            'meeting_code' => 'AYK-INV001',
            'name' => 'Jane Host',
            'email' => 'host@example.test',
            'phone' => '123456',
            'meeting_topic' => 'Partner sync',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'scheduled',
            'meeting_type' => 'online',
            'is_online' => true,
            'is_multi_company' => true,
        ], $attributes));
    }

    private function invitee(Meeting $meeting, array $attributes = []): Attendee
    {
        $host = MeetingCompany::forName($meeting, 'Aykon HQ', true);
        $company = MeetingCompany::forName($meeting, 'Acme Ltd');

        return Attendee::create(array_merge([
            'meeting_id' => $meeting->id,
            'meeting_company_id' => $company->id,
            'department' => 'Operations',
            'full_name' => 'Ravi Patel',
            'email' => 'ravi@acme.test',
            'phone' => '5550102',
            'invitation_status' => 'pending',
        ], $attributes));
    }

    public function test_invitation_page_renders_for_an_external_invitee(): void
    {
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting);
        MeetingCompany::forName($meeting, 'Contoso');

        $this->assertNotEmpty($attendee->invitation_token);

        $this->get(route('invitations.show', $attendee->invitation_token))
            ->assertOk()
            ->assertSee('You are invited')
            ->assertSee('Partner sync')
            ->assertSee('Acme Ltd')
            ->assertSee('Contoso')
            ->assertSee('Accept invitation')
            ->assertSee('Decline');
    }

    public function test_invalid_invitation_token_returns_not_found(): void
    {
        $this->get(route('invitations.show', 'does-not-exist'))->assertNotFound();
    }

    public function test_invitee_can_accept_and_receives_confirmation(): void
    {
        $meeting = $this->meeting(['status' => 'in_progress', 'started_at' => now()]);
        $attendee = $this->invitee($meeting);

        $this->post(route('invitations.accept', $attendee->invitation_token))
            ->assertRedirect(route('invitations.confirmed', $attendee->invitation_token));

        $attendee->refresh();
        $this->assertSame('accepted', $attendee->invitation_status);
        $this->assertNotNull($attendee->invitation_responded_at);
        $this->assertSame('accepted', $attendee->meetingCompany->status);

        $this->get(route('invitations.confirmed', $attendee->invitation_token))
            ->assertOk()
            ->assertSee('confirmed')
            ->assertSee('Join Meeting');
    }

    public function test_invitee_can_decline(): void
    {
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting);

        $this->post(route('invitations.decline', $attendee->invitation_token))
            ->assertOk()
            ->assertSee('Response recorded');

        $attendee->refresh();
        $this->assertSame('declined', $attendee->invitation_status);
        $this->assertSame('declined', $attendee->meetingCompany->status);
    }

    public function test_registered_invitee_must_sign_in_before_responding(): void
    {
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting);
        User::factory()->create(['email' => 'ravi@acme.test']);

        $this->get(route('invitations.show', $attendee->invitation_token))
            ->assertRedirect(route('login'));

        $this->actingAs(User::where('email', 'ravi@acme.test')->firstOrFail())
            ->get(route('invitations.show', $attendee->invitation_token))
            ->assertOk()
            ->assertSee('You are invited');
    }

    public function test_invitation_owned_by_another_account_is_forbidden(): void
    {
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting);
        User::factory()->create(['email' => 'ravi@acme.test']);

        $this->actingAs(User::factory()->create(['email' => 'someone@else.test']))
            ->get(route('invitations.show', $attendee->invitation_token))
            ->assertForbidden();
    }

    public function test_cancelled_meeting_invitation_shows_error(): void
    {
        $meeting = $this->meeting(['status' => 'cancelled', 'cancelled_at' => now()]);
        $attendee = $this->invitee($meeting);

        $this->get(route('invitations.show', $attendee->invitation_token))
            ->assertStatus(410)
            ->assertSee('Invitation cancelled');
    }

    public function test_expired_invitation_shows_error(): void
    {
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting, ['invitation_expires_at' => now()->subDay()]);

        $this->get(route('invitations.show', $attendee->invitation_token))
            ->assertStatus(410)
            ->assertSee('Invitation expired');
    }

    public function test_join_state_waits_for_host_until_meeting_starts(): void
    {
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting, ['invitation_status' => 'accepted']);

        $this->assertSame('waiting', $meeting->inviteeJoinState());

        $this->get(route('invitations.confirmed', $attendee->invitation_token))
            ->assertOk()
            ->assertSee('Waiting for the host');

        $this->get(route('invitations.lobby', $attendee->invitation_token))
            ->assertRedirect(route('invitations.confirmed', $attendee->invitation_token));
    }

    public function test_accepted_invitee_can_enter_lobby_and_join_the_shared_room(): void
    {
        $meeting = $this->meeting(['status' => 'in_progress', 'started_at' => now()]);
        $attendee = $this->invitee($meeting, ['invitation_status' => 'accepted']);

        $this->assertSame('open', $meeting->inviteeJoinState());

        $this->get(route('invitations.lobby', $attendee->invitation_token))
            ->assertOk()
            ->assertSee('Pre-join lobby')
            ->assertSee('Join Now');

        $response = $this->get(route('invitations.enter', $attendee->invitation_token));
        $response->assertRedirect();
        $target = $response->getTargetUrl();
        $this->assertStringContainsString('/join-session', $target);
        $this->assertStringContainsString('invite='.$attendee->invitation_token, $target);

        $this->get($target)
            ->assertOk()
            ->assertSee('data-direct-join', false);

        $participant = OnlineSessionParticipant::where('meeting_id', $meeting->id)->firstOrFail();
        $this->assertSame($attendee->id, $participant->attendee_id);
        $this->assertSame('Ravi Patel', $participant->display_name);
        $this->assertFalse($participant->is_host);
    }

    public function test_in_person_invitation_confirmation_shows_location_without_join(): void
    {
        $meeting = $this->meeting([
            'meeting_type' => 'in_person',
            'is_online' => false,
            'physical_location' => 'Grand Hall A',
        ]);
        $attendee = $this->invitee($meeting, ['invitation_status' => 'accepted']);

        $this->get(route('invitations.confirmed', $attendee->invitation_token))
            ->assertOk()
            ->assertSee('Grand Hall A')
            ->assertSee('in person')
            ->assertDontSee('Join Meeting');
    }

    public function test_state_endpoint_reports_join_state(): void
    {
        $meeting = $this->meeting(['status' => 'in_progress', 'started_at' => now()]);
        $attendee = $this->invitee($meeting, ['invitation_status' => 'accepted']);

        $this->getJson(route('invitations.state', $attendee->invitation_token))
            ->assertOk()
            ->assertJson(['state' => 'open', 'accepted' => true]);
    }

    public function test_invitee_cannot_open_the_organizer_dashboard(): void
    {
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting);

        $this->get(route('meetings.details', $meeting))->assertRedirect(route('login'));

        $this->get(route('invitations.show', $attendee->invitation_token))
            ->assertOk()
            ->assertDontSee('Invitation Management');
    }

    public function test_invitation_email_includes_personal_link(): void
    {
        Mail::fake();
        $meeting = $this->meeting();

        $this->actingAs(User::factory()->create())
            ->post(route('meetings.companies.store', $meeting), [
                'name' => 'Acme Ltd',
                'rep_name' => 'Ravi Patel',
                'rep_email' => 'ravi@acme.test',
                'rep_phone' => '5550102',
            ]);

        $attendee = $meeting->attendees()->where('email', 'ravi@acme.test')->firstOrFail();

        Mail::assertSent(AttendeeInvitation::class, function (AttendeeInvitation $mail) use ($attendee) {
            return $mail->hasTo('ravi@acme.test')
                && str_contains($mail->invitationUrl, $attendee->invitation_token)
                && str_contains($mail->invitationUrl, '/invitation/');
        });
    }
}
