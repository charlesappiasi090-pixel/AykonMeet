<?php

namespace Tests\Feature;

use App\Mail\AttendeeInvitation;
use App\Mail\InvitationReminder;
use App\Mail\InvitationResponse;
use App\Mail\MeetingRescheduled;
use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\MeetingCompany;
use App\Models\User;
use App\Notifications\InvitationResponseReceived;
use App\Notifications\MeetingInvitationReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CompanyInvitationTest extends TestCase
{
    use RefreshDatabase;

    private function meeting(array $attributes = []): Meeting
    {
        return Meeting::create(array_merge([
            'meeting_code' => 'AYK-COI001',
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
        ], $attributes));
    }

    private function invitee(Meeting $meeting, array $attributes = []): Attendee
    {
        MeetingCompany::forName($meeting, 'Aykon HQ', true);
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

    public function test_sending_an_invitation_marks_it_sent_and_records_a_timestamp(): void
    {
        Mail::fake();
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting);

        $this->actingAs(User::factory()->create())
            ->post(route('meetings.invitations.send', $meeting))
            ->assertRedirect();

        $attendee->refresh();
        $this->assertNotNull($attendee->invitation_sent_at);
        $this->assertSame('sent', $attendee->invitationState());
        Mail::assertSent(AttendeeInvitation::class, fn (AttendeeInvitation $mail) => $mail->hasTo('ravi@acme.test'));
    }

    public function test_registered_recipient_receives_an_in_app_notification(): void
    {
        Mail::fake();
        Notification::fake();
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting);
        $user = User::factory()->nonAdmin()->create(['email' => 'ravi@acme.test']);

        $this->actingAs(User::factory()->create())
            ->post(route('meetings.invitations.send', $meeting));

        Notification::assertSentTo($user, MeetingInvitationReceived::class);
    }

    public function test_opening_the_invitation_marks_it_delivered(): void
    {
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting);

        $this->get(route('invitations.show', $attendee->invitation_token))->assertOk();

        $attendee->refresh();
        $this->assertNotNull($attendee->invitation_delivered_at);
        $this->assertSame('delivered', $attendee->invitationState());
    }

    public function test_accepting_verifies_the_invitee_and_notifies_the_organizer(): void
    {
        Mail::fake();
        Notification::fake();
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting);
        $admin = User::factory()->create();

        $this->post(route('invitations.accept', $attendee->invitation_token))
            ->assertRedirect(route('invitations.confirmed', $attendee->invitation_token));

        $attendee->refresh();
        $this->assertSame('accepted', $attendee->invitationState());
        $this->assertNotNull($attendee->invitation_verified_at);

        Notification::assertSentTo($admin, InvitationResponseReceived::class);
        Mail::assertSent(InvitationResponse::class, fn (InvitationResponse $mail) => $mail->hasTo('host@example.test'));
    }

    public function test_declining_notifies_the_organizer(): void
    {
        Mail::fake();
        Notification::fake();
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting);

        $this->post(route('invitations.decline', $attendee->invitation_token))
            ->assertOk()
            ->assertSee('Response recorded');

        $attendee->refresh();
        $this->assertSame('declined', $attendee->invitationState());
        Mail::assertSent(InvitationResponse::class);
    }

    public function test_reminders_are_only_sent_to_pending_invitees(): void
    {
        Mail::fake();
        $meeting = $this->meeting();
        $pending = $this->invitee($meeting);
        $accepted = $this->invitee($meeting, ['full_name' => 'Ada', 'email' => 'ada@acme.test', 'invitation_status' => 'accepted']);

        $this->actingAs(User::factory()->create())
            ->post(route('meetings.invitations.remind', $meeting))
            ->assertRedirect();

        $this->assertNotNull($pending->fresh()->reminder_sent_at);
        $this->assertNull($accepted->fresh()->reminder_sent_at);
        Mail::assertSent(InvitationReminder::class, fn (InvitationReminder $mail) => $mail->hasTo('ravi@acme.test'));
    }

    public function test_rescheduling_notifies_every_non_declined_attendee(): void
    {
        Mail::fake();
        $meeting = $this->meeting();
        $this->invitee($meeting);
        $this->invitee($meeting, ['full_name' => 'Declined', 'email' => 'no@acme.test', 'invitation_status' => 'declined']);

        $this->actingAs(User::factory()->create())
            ->patch(route('meetings.reschedule', $meeting), [
                'start_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
                'end_date' => now()->addDays(2)->addHour()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect();

        Mail::assertSent(MeetingRescheduled::class, 1);
        Mail::assertSent(MeetingRescheduled::class, fn (MeetingRescheduled $mail) => $mail->hasTo('ravi@acme.test'));
    }

    public function test_colleague_invites_are_blocked_until_the_host_enables_them(): void
    {
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting, ['invitation_status' => 'accepted']);

        $this->post(route('invitations.colleague', $attendee->invitation_token), [
            'full_name' => 'New Colleague',
            'email' => 'new@acme.test',
        ])->assertForbidden();
    }

    public function test_enabled_self_invites_create_a_draft_pending_host_approval(): void
    {
        $meeting = $this->meeting(['settings' => ['company_self_invite' => true]]);
        $attendee = $this->invitee($meeting, ['invitation_status' => 'accepted']);

        $this->post(route('invitations.colleague', $attendee->invitation_token), [
            'full_name' => 'New Colleague',
            'email' => 'new@acme.test',
            'department' => 'Finance',
        ])->assertRedirect();

        $colleague = $meeting->attendees()->where('email', 'new@acme.test')->firstOrFail();
        $this->assertTrue($colleague->requires_approval);
        $this->assertSame('draft', $colleague->invitationState());
    }

    public function test_host_can_approve_a_requested_colleague(): void
    {
        Mail::fake();
        $meeting = $this->meeting(['settings' => ['company_self_invite' => true]]);
        $attendee = $this->invitee($meeting, ['invitation_status' => 'accepted']);

        $this->post(route('invitations.colleague', $attendee->invitation_token), [
            'full_name' => 'New Colleague',
            'email' => 'new@acme.test',
        ]);

        $colleague = $meeting->attendees()->where('email', 'new@acme.test')->firstOrFail();

        $this->actingAs(User::factory()->create())
            ->post(route('attendees.approve', $colleague))
            ->assertRedirect();

        $colleague->refresh();
        $this->assertFalse($colleague->requires_approval);
        $this->assertNotNull($colleague->invitation_sent_at);
        Mail::assertSent(AttendeeInvitation::class, fn (AttendeeInvitation $mail) => $mail->hasTo('new@acme.test'));
    }

    public function test_self_invites_respect_the_participant_limit(): void
    {
        $meeting = $this->meeting(['settings' => ['company_self_invite' => true, 'max_participants' => 1]]);
        $attendee = $this->invitee($meeting, ['invitation_status' => 'accepted']);

        $this->post(route('invitations.colleague', $attendee->invitation_token), [
            'full_name' => 'New Colleague',
            'email' => 'new@acme.test',
        ])->assertSessionHas('invitation_error');

        $this->assertDatabaseMissing('attendees', ['email' => 'new@acme.test']);
    }

    public function test_company_aggregate_state_rolls_up_representatives(): void
    {
        $meeting = $this->meeting();
        $accepted = $this->invitee($meeting);
        $accepted->forceFill(['invitation_status' => 'accepted'])->save();
        $this->invitee($meeting, ['full_name' => 'Ada', 'email' => 'ada@acme.test']);

        $company = $accepted->meetingCompany;
        $this->assertSame('accepted', $company->fresh()->aggregateInvitationState());
        $this->assertSame('Accepted', $company->aggregateInvitationLabel());
    }

    public function test_dashboard_displays_tracking_states_and_actions(): void
    {
        $meeting = $this->meeting();
        $this->invitee($meeting);

        $this->actingAs(User::factory()->create())
            ->get(route('meetings.details', $meeting))
            ->assertOk()
            ->assertSee('Send Reminders')
            ->assertSee('Draft');
    }

    public function test_invitation_email_includes_full_meeting_details(): void
    {
        Mail::fake();
        $meeting = $this->meeting([
            'description' => 'Quarterly partner alignment across the consortium.',
            'start_date' => now()->addDay()->setTime(9, 0),
            'end_date' => now()->addDay()->setTime(10, 30),
        ]);
        $attendee = $this->invitee($meeting);

        \Log::info('Test: Before post request');
        $response = $this->actingAs(User::factory()->create())
            ->post(route('meetings.invitations.send', $meeting));
        \Log::info('Test: After post request', ['status' => $response->getStatusCode()]);

        $response->assertRedirect();

        \Log::info('Test: Checking sent mail', ['sent_count' => Mail::sent(AttendeeInvitation::class)->count()]);

        Mail::assertSent(AttendeeInvitation::class, function (AttendeeInvitation $mail) use ($attendee) {
            \Log::info('Mail assertSent callback called');
            try {
                $html = $mail->render();
                \Log::info('Mail rendered successfully', ['html_length' => strlen($html)]);

                $invitationToken = $attendee->invitation_token;
                \Log::info('Checking invitation token in HTML', ['token' => $invitationToken]);

                $checks = [
                    'hasTo' => $mail->hasTo('ravi@acme.test'),
                    'hasPartnerSync' => str_contains($html, 'Partner sync'),
                    'hasJaneHost' => str_contains($html, 'Jane Host'),
                    'hasDuration' => str_contains($html, '1h 30m'),
                    'hasDescription' => str_contains($html, 'Quarterly partner alignment across the consortium.'),
                    'hasAcmeLtd' => str_contains($html, 'Acme Ltd'),
                    'hasInvitationToken' => str_contains($html, $invitationToken),
                ];

                \Log::info('Render checks', $checks);

                return $checks['hasTo']
                    && $checks['hasPartnerSync']
                    && $checks['hasJaneHost']
                    && $checks['hasDuration']
                    && $checks['hasDescription']
                    && $checks['hasAcmeLtd']
                    && $checks['hasInvitationToken'];
            } catch (\Throwable $e) {
                \Log::error('Error in mail callback', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return false;
            }
        });
    }

    public function test_responding_twice_does_not_send_duplicate_organizer_notifications(): void
    {
        Mail::fake();
        Notification::fake();
        $meeting = $this->meeting();
        $attendee = $this->invitee($meeting);

        $this->post(route('invitations.accept', $attendee->invitation_token))
            ->assertRedirect(route('invitations.confirmed', $attendee->invitation_token));

        $this->post(route('invitations.accept', $attendee->invitation_token))
            ->assertRedirect(route('invitations.confirmed', $attendee->invitation_token));

        Mail::assertSent(InvitationResponse::class, 1);
    }
}
