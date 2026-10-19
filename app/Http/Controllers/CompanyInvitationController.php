<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\CompanyMembership;
use App\Models\Meeting;
use App\Models\MeetingCompany;
use App\Models\ParticipantApproval;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CompanyInvitationController extends Controller
{
    /**
     * The public landing page for a company's invitation link. Every employee
     * who opens the link must verify their identity before they are added to
     * the meeting under the company that owns this token.
     */
    public function show(Request $request, string $token): View|Response
    {
        $company = MeetingCompany::findByInvitationToken($token);
        abort_unless($company && $company->isInvitationUsable(), 410, 'This invitation link is no longer valid.');

        $meeting = $company->meeting;
        abort_if($meeting === null, 404);

        if ($error = $this->guardMeeting($meeting)) {
            return $error;
        }

        $company->markInvitationUsed();

        return view('invitations.company', [
            'company' => $company,
            'meeting' => $meeting,
            'token' => $token,
            'prefillName' => auth()->user()?->name,
            'prefillEmail' => auth()->user()?->email,
            'error' => $request->session()->get('invitation_error'),
        ]);
    }

    /**
     * Verify the employee's identity and assign them to the company that owns
     * the link. The company is always resolved server-side from the token; the
     * request can never choose or switch a company.
     */
    public function verify(Request $request, string $token): RedirectResponse|Response
    {
        $company = MeetingCompany::findByInvitationToken($token);
        abort_unless($company && $company->isInvitationUsable(), 410, 'This invitation link is no longer valid.');

        $meeting = $company->meeting;
        abort_if($meeting === null, 404);

        if ($error = $this->guardMeeting($meeting)) {
            return $error;
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255'],
            'department' => ['nullable', 'string', 'max:160'],
        ]);

        $email = strtolower(trim($data['email']));
        $isKnownMember = $this->isKnownMember($company, $email);

        $company->memberships()->firstOrCreate(
            ['email' => $email],
            [
                'user_id' => auth()->id(),
                'full_name' => $data['full_name'],
                'department' => $data['department'] ?? null,
                'role' => CompanyMembership::ROLE_MEMBER,
                'status' => $isKnownMember ? 'verified' : 'invited',
                'verified_at' => $isKnownMember ? now() : null,
            ],
        );

        $attendee = $meeting->attendees()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        $mismatch = $attendee !== null
            && $attendee->meeting_company_id !== null
            && (int) $attendee->meeting_company_id !== (int) $company->id;

        if ($attendee === null) {
            $attendee = $meeting->attendees()->create([
                'meeting_company_id' => $company->id,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'department' => $data['department'] ?? null,
                'phone' => '0000000000',
                'invitation_status' => 'draft',
                'requires_approval' => ! $isKnownMember,
            ]);
        } else {
            $attendee->fill([
                'full_name' => $data['full_name'],
                'department' => $data['department'] ?? $attendee->department,
            ]);
            if ($mismatch || ! $isKnownMember) {
                $attendee->requires_approval = true;
            }
            $attendee->save();
        }

        if (! $isKnownMember || $mismatch) {
            ParticipantApproval::create([
                'meeting_id' => $meeting->id,
                'attendee_id' => $attendee->id,
                'requested_company_id' => $company->id,
                'current_company_id' => $mismatch ? $attendee->meeting_company_id : null,
                'type' => $mismatch
                    ? ParticipantApproval::TYPE_MISMATCH
                    : ParticipantApproval::TYPE_GUEST,
                'status' => ParticipantApproval::STATUS_PENDING,
                'email' => $email,
                'display_name' => $data['full_name'],
                'requested_by_user_id' => auth()->id(),
            ]);
        }

        if ($mismatch) {
            return redirect()
                ->route('invitations.show', ['attendee' => $attendee->invitation_token])
                ->with('invitation_success', 'Your request to join as '.$company->name.' is pending host approval.');
        }

        return redirect()
            ->route('invitations.show', ['attendee' => $attendee->invitation_token])
            ->with('invitation_success', 'You have been added to '.$company->name.'. Review and confirm your invitation.');
    }

    /**
     * Decline the invitation on behalf of the company.
     */
    public function decline(Request $request, string $token): RedirectResponse|Response
    {
        $company = MeetingCompany::findByInvitationToken($token);
        abort_unless($company, 404, 'This invitation link is no longer valid.');

        $company->update([
            'invitation_status' => 'declined',
        ]);

        return redirect()->route('company-invitations.show', ['token' => $token])
            ->with('status_message', 'You have declined this invitation. If this was in error, you may still accept below.');
    }

    private function isKnownMember(MeetingCompany $company, string $email): bool
    {
        if ($company->contact_email && strtolower($company->contact_email) === $email) {
            return true;
        }

        if ($company->memberships()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            return true;
        }

        return Attendee::query()
            ->where('meeting_company_id', $company->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->exists();
    }

    private function guardMeeting(Meeting $meeting): ?Response
    {
        if ($meeting->isCancelled()) {
            return $this->errorView(
                'Invitation cancelled',
                'This meeting was cancelled by the organiser, so this company link is no longer valid.',
                410,
            );
        }

        if ($meeting->ended_at !== null || in_array($meeting->status, ['completed'], true)) {
            return $this->errorView(
                'Invitation expired',
                'This meeting has already ended, so this company link is no longer valid.',
                410,
            );
        }

        return null;
    }

    private function errorView(string $title, string $message, int $status): Response
    {
        return response()->view('invitations.error', compact('title', 'message'), $status);
    }
}
