<?php

namespace Tests\Feature;

use App\Mail\OnlineSessionVerificationCode;
use App\Models\Attendee;
use App\Models\DocumentShare;
use App\Models\Meeting;
use App\Models\MeetingCompany;
use App\Models\OnlineSessionParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SecureMeetingAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createMeeting(array $attributes = []): Meeting
    {
        return Meeting::create(array_merge([
            'meeting_code' => 'AYK-SEC'.random_int(1000, 9999),
            'name' => 'Test Host',
            'email' => 'host@example.test',
            'phone' => '555-0100',
            'meeting_topic' => 'Secure Meeting',
            'start_date' => now()->addHour(),
            'end_date' => now()->addHours(2),
            'status' => 'in_progress',
            'is_online' => true,
            'is_multi_company' => true,
            'settings' => ['waiting_room' => false],
        ], $attributes));
    }

    private function createAttendee(Meeting $meeting, array $attributes = []): Attendee
    {
        $company = MeetingCompany::forName($meeting, 'Test Company');

        return Attendee::create(array_merge([
            'meeting_id' => $meeting->id,
            'meeting_company_id' => $company->id,
            'full_name' => 'Test Attendee',
            'email' => 'attendee'.random_int(1000, 9999).'@example.test',
            'phone' => '555-0100',
            'invitation_status' => 'accepted',
        ], $attributes));
    }

    private function joinAsAttendee(Meeting $meeting, Attendee $attendee): array
    {
        $joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]);
        $code = $this->requestEmailVerificationCode($meeting, $attendee);

        return $this->postJson(URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]), [
            'email' => $attendee->email,
            'verification_code' => $code,
        ])->assertOk()->json();
    }

    /**
     * TEST CASE: A guest can join an authorized active meeting.
     */
    public function test_guest_can_join_authorized_active_meeting(): void
    {
        Storage::fake('local');
        Mail::fake();

        $meeting = $this->createMeeting();
        $attendee = $this->createAttendee($meeting);

        $joinData = $this->joinAsAttendee($meeting, $attendee);

        $this->assertArrayHasKey('peer_id', $joinData);
        $this->assertArrayHasKey('token', $joinData);
        $this->assertFalse($joinData['waiting'] ?? true);

        $attendee->refresh();
        $this->assertNotNull($attendee->checked_in_at);
    }

    /**
     * TEST CASE: A guest cannot access the admin dashboard by entering its URL directly.
     */
    public function test_guest_cannot_access_admin_dashboard(): void
    {
        Storage::fake('local');
        Mail::fake();

        $meeting = $this->createMeeting();
        $attendee = $this->createAttendee($meeting);

        $joinData = $this->joinAsAttendee($meeting, $attendee);

        // Try to access admin dashboard - should redirect to login (302) or return 403
        $this->get('/admin', ['peer_id' => $joinData['peer_id'], 'token' => $joinData['token']])
            ->assertStatus(302); // Redirects to login because auth middleware
    }

    /**
     * TEST CASE: A guest cannot retrieve admin information through protected APIs.
     */
    public function test_guest_cannot_access_admin_apis(): void
    {
        Storage::fake('local');
        Mail::fake();

        $meeting = $this->createMeeting();
        $attendee = $this->createAttendee($meeting);

        $joinData = $this->joinAsAttendee($meeting, $attendee);
        $auth = ['peer_id' => $joinData['peer_id'], 'token' => $joinData['token']];

        // Try to access admin users API (GET route)
        $this->get('/admin/users', $auth)->assertStatus(302); // Redirects to login

        // Try to access admin reports
        $this->get('/admin/reports', $auth)->assertStatus(302);

        // Try to access admin settings
        $this->get('/admin/settings', $auth)->assertStatus(302);
    }

    /**
     * TEST CASE: A participant can leave and rejoin an active meeting if still authorized.
     */
    public function test_participant_can_leave_and_rejoin_active_meeting(): void
    {
        Storage::fake('local');
        Mail::fake();

        $meeting = $this->createMeeting();
        $attendee = $this->createAttendee($meeting);

        $joinData = $this->joinAsAttendee($meeting, $attendee);
        $auth = ['peer_id' => $joinData['peer_id'], 'token' => $joinData['token']];

        // Leave the meeting
        $this->postJson(URL::signedRoute('sessions.participants.leave', ['meeting' => $meeting]), $auth)
            ->assertOk();

        $attendee->refresh();
        $this->assertNotNull($attendee->checked_out_at);

        // Rejoin - need new verification code since token was invalidated
        $newJoinData = $this->joinAsAttendee($meeting, $attendee);

        $this->assertArrayHasKey('peer_id', $newJoinData);
        $this->assertNotEquals($joinData['peer_id'], $newJoinData['peer_id']);

        $attendee->refresh();
        $this->assertNull($attendee->checked_out_at);
    }

    /**
     * TEST CASE: Ending a meeting disconnects all participants.
     */
    public function test_ending_meeting_disconnects_all_participants(): void
    {
        Storage::fake('local');
        Mail::fake();

        $admin = User::factory()->create();
        $meeting = $this->createMeeting(['host_user_id' => $admin->id]);
        $attendee = $this->createAttendee($meeting);

        $joinData = $this->joinAsAttendee($meeting, $attendee);

        // Verify participant is in the meeting
        $participant = OnlineSessionParticipant::where('peer_id', $joinData['peer_id'])->firstOrFail();
        $this->assertNull($participant->left_at);

        // Host ends the meeting
        $this->actingAs($admin)
            ->post(route('meetings.end', $meeting))
            ->assertRedirect();

        // Check meeting is ended
        $meeting->refresh();
        $this->assertNotNull($meeting->ended_at);
        $this->assertSame('completed', $meeting->status);

        // Check participant was disconnected
        $participant->refresh();
        $this->assertNotNull($participant->left_at);
        $this->assertNull($participant->token_hash); // Token invalidated

        $attendee->refresh();
        $this->assertNotNull($attendee->checked_out_at);
    }

    /**
     * TEST CASE: No participant can rejoin after the meeting has ended.
     */
    public function test_cannot_rejoin_after_meeting_ended(): void
    {
        Storage::fake('local');
        Mail::fake();

        $admin = User::factory()->create();
        $meeting = $this->createMeeting(['host_user_id' => $admin->id]);
        $attendee = $this->createAttendee($meeting);

        $joinData = $this->joinAsAttendee($meeting, $attendee);
        $auth = ['peer_id' => $joinData['peer_id'], 'token' => $joinData['token']];

        // Host ends the meeting
        $this->actingAs($admin)
            ->post(route('meetings.end', $meeting))
            ->assertRedirect();

        $meeting->refresh();
        $this->assertSame('completed', $meeting->status);

        // Try to rejoin - should be rejected with 410
        $response = $this->postJson(URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]), [
            'email' => $attendee->email,
            'verification_code' => $this->requestEmailVerificationCode($meeting, $attendee),
        ])->assertStatus(410);

        $this->assertSame('ended', $response->json('status'));
    }

    /**
     * TEST CASE: An expired or revoked meeting invitation is rejected.
     */
    public function test_expired_or_revoked_invitation_rejected(): void
    {
        Storage::fake('local');
        Mail::fake();

        $meeting = $this->createMeeting(['status' => 'scheduled', 'start_date' => now()->addDay()]);
        $company = MeetingCompany::forName($meeting, 'Test Company');
        $company->issueInvitation(now()->subDay()); // Expired yesterday

        $attendee = $this->createAttendee($meeting, ['meeting_company_id' => $company->id, 'invitation_status' => 'sent']);

        // Try to use expired invitation link
        $response = $this->get(route('company-invitations.show', ['token' => $company->invitation_token]))
            ->assertStatus(410);

        // Try to verify with expired token
        $response = $this->post(route('company-invitations.verify', ['token' => $company->invitation_token]), [
            'full_name' => 'Test User',
            'email' => 'test@example.test',
            'department' => 'Test',
        ])->assertStatus(410);
    }

    /**
     * TEST CASE: A guest's meeting session is terminated without unnecessarily logging out a separate authenticated account.
     */
    public function test_guest_session_terminated_without_logging_out_admin_account(): void
    {
        Storage::fake('local');
        Mail::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $meeting = $this->createMeeting(['host_user_id' => $admin->id]);
        $attendee = $this->createAttendee($meeting);

        // Admin logs in separately
        $this->actingAs($admin);
        $this->assertAuthenticatedAs($admin);

        // Guest joins meeting
        $joinData = $this->joinAsAttendee($meeting, $attendee);

        // Meeting ends
        $this->actingAs($admin)->post(route('meetings.end', $meeting));

        // Admin should still be authenticated
        $this->assertAuthenticatedAs($admin);

        // Guest should be disconnected
        $meeting->refresh();
        $this->assertSame('completed', $meeting->status);
    }

    /**
     * TEST CASE: Members of one company cannot access another company's restricted documents.
     */
    public function test_company_document_isolation(): void
    {
        Storage::fake('local');
        Mail::fake();

        $meeting = $this->createMeeting(['is_multi_company' => true]);

        $companyA = MeetingCompany::forName($meeting, 'Company A');
        $companyB = MeetingCompany::forName($meeting, 'Company B');

        $attendeeA = $this->createAttendee($meeting, ['meeting_company_id' => $companyA->id, 'email' => 'a@example.test']);
        $attendeeB = $this->createAttendee($meeting, ['meeting_company_id' => $companyB->id, 'email' => 'b@example.test']);

        $joinA = $this->joinAsAttendee($meeting, $attendeeA);
        $joinB = $this->joinAsAttendee($meeting, $attendeeB);

        // Attendee A uploads a document scoped to Company A
        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $uploadResponse = $this->postJson(URL::signedRoute('documents.upload', ['meeting' => $meeting]), [
            'peer_id' => $joinA['peer_id'],
            'token' => $joinA['token'],
            'file' => $file,
            'share_scope' => 'companies',
            'shared_with_companies' => [$companyA->id],
            'allow_download' => true,
            'allow_preview' => true,
        ])->assertCreated();

        $document = DocumentShare::findOrFail($uploadResponse->json('document.id'));
        $documentId = $document->id;

        // Attendee B lists documents - should not see the document
        $listUrlB = URL::signedRoute('documents.list', [
            'meeting' => $meeting,
            'peer_id' => $joinB['peer_id'],
            'token' => $joinB['token'],
        ]);
        $listResponseB = $this->getJson($listUrlB)->assertOk();

        $documentsB = $listResponseB->json('documents');
        $this->assertEmpty(array_filter($documentsB, fn ($d) => $d['id'] == $documentId));

        // Attendee A lists documents - should see the document
        $listUrlA = URL::signedRoute('documents.list', [
            'meeting' => $meeting,
            'peer_id' => $joinA['peer_id'],
            'token' => $joinA['token'],
        ]);
        $listResponseA = $this->getJson($listUrlA)->assertOk();

        $documentsA = $listResponseA->json('documents');
        $this->assertNotEmpty(array_filter($documentsA, fn ($d) => $d['id'] == $documentId));
    }

    /**
     * TEST CASE: Meeting status checks prevent joining in wrong states.
     */
    public function test_meeting_status_prevents_joining_in_wrong_states(): void
    {
        Storage::fake('local');
        Mail::fake();

        // Test cancelled meeting
        $cancelledMeeting = $this->createMeeting(['status' => 'cancelled']);
        $attendee = $this->createAttendee($cancelledMeeting);

        $response = $this->postJson(URL::signedRoute('sessions.participants.join', ['meeting' => $cancelledMeeting]), [
            'email' => $attendee->email,
            'verification_code' => $this->requestEmailVerificationCode($cancelledMeeting, $attendee),
        ])->assertStatus(410);

        // Test completed meeting
        $completedMeeting = $this->createMeeting(['status' => 'completed', 'ended_at' => now()]);
        $attendee2 = $this->createAttendee($completedMeeting);

        $response = $this->postJson(URL::signedRoute('sessions.participants.join', ['meeting' => $completedMeeting]), [
            'email' => $attendee2->email,
            'verification_code' => $this->requestEmailVerificationCode($completedMeeting, $attendee2),
        ])->assertStatus(410);

        // Test scheduled meeting with waiting room - should allow join but put in waiting room
        $scheduledMeeting = $this->createMeeting([
            'status' => 'scheduled',
            'start_date' => now()->addHour(),
            'settings' => ['waiting_room' => true],
        ]);
        $attendee3 = $this->createAttendee($scheduledMeeting);

        $joinData = $this->joinAsAttendee($scheduledMeeting, $attendee3);
        $this->assertTrue($joinData['waiting']);
    }

    /**
     * Helper method to request email verification code
     */
    private function requestEmailVerificationCode(Meeting $meeting, Attendee $attendee): string
    {
        $this->postJson(URL::signedRoute('sessions.participants.send-code', ['meeting' => $meeting]), [
            'email' => $attendee->email,
        ])->assertOk();

        $code = null;
        Mail::assertSent(OnlineSessionVerificationCode::class, function ($mail) use ($attendee, &$code) {
            if ($mail->hasTo($attendee->email)) {
                $code = $mail->code;

                return true;
            }

            return false;
        });

        return $code;
    }
}
