<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\User;
use App\Services\InvitationNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function show(Request $request, Attendee $attendee): View|RedirectResponse|Response
    {
        if ($redirect = $this->authorizeInvitee($request, $attendee)) {
            return $redirect;
        }

        $meeting = $this->meeting($attendee);

        if ($error = $this->guardMeeting($meeting, $attendee)) {
            return $error;
        }

        $attendee->markDelivered();

        if ($attendee->invitationStatus() === 'accepted') {
            return redirect()->route('invitations.confirmed', ['attendee' => $attendee->invitation_token]);
        }

        return view('invitations.show', $this->payload($attendee, $meeting));
    }

    public function accept(Request $request, Attendee $attendee): View|RedirectResponse|Response
    {
        if ($redirect = $this->authorizeInvitee($request, $attendee)) {
            return $redirect;
        }

        $meeting = $this->meeting($attendee);

        if ($error = $this->guardMeeting($meeting, $attendee)) {
            return $error;
        }

        if ($attendee->invitationStatus() !== 'accepted') {
            $attendee->update([
                'invitation_status' => 'accepted',
                'invitation_responded_at' => now(),
            ]);
            $attendee->markVerified();

            $attendee->meetingCompany?->update(['status' => 'accepted']);

            app(InvitationNotificationService::class)->organizerResponded($meeting, $attendee, 'accepted');
        }

        return redirect()
            ->route('invitations.confirmed', ['attendee' => $attendee->invitation_token])
            ->with('invitation_success', 'Thank you '.$attendee->full_name.', your attendance is confirmed.');
    }

    public function decline(Request $request, Attendee $attendee): View|RedirectResponse|Response
    {
        if ($redirect = $this->authorizeInvitee($request, $attendee)) {
            return $redirect;
        }

        $meeting = $this->meeting($attendee);

        if ($error = $this->guardMeeting($meeting, $attendee)) {
            return $error;
        }

        if ($attendee->invitationStatus() !== 'declined') {
            $attendee->update([
                'invitation_status' => 'declined',
                'invitation_responded_at' => now(),
            ]);
            $attendee->markVerified();

            $attendee->meetingCompany?->update(['status' => 'declined']);

            app(InvitationNotificationService::class)->organizerResponded($meeting, $attendee, 'declined');
        }

        return view('invitations.declined', $this->payload($attendee, $meeting));
    }

    public function inviteColleague(Request $request, Attendee $attendee): RedirectResponse|Response
    {
        if ($redirect = $this->authorizeInvitee($request, $attendee)) {
            return $redirect;
        }

        $meeting = $this->meeting($attendee);

        if ($error = $this->guardMeeting($meeting, $attendee)) {
            return $error;
        }

        abort_unless($attendee->invitationStatus() === 'accepted', 403, 'Accept your invitation before inviting colleagues.');
        abort_unless(
            $attendee->meetingCompany && ! $attendee->meetingCompany->is_host_company,
            403,
            'Only invited companies can add colleagues to this meeting.'
        );
        abort_unless(
            (bool) $meeting->setting('company_self_invite'),
            403,
            'The host has not enabled company self-invitations for this meeting.'
        );

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160'],
            'department' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $max = $meeting->setting('max_participants');
        if ($max !== null && $meeting->attendees()->count() >= (int) $max) {
            return back()->with('invitation_error', 'This meeting has reached its participant limit.');
        }

        if ($meeting->attendees()->where('email', $data['email'])->exists()) {
            return back()->with('invitation_error', 'That colleague has already been invited to this meeting.');
        }

        $digits = preg_replace('/\D+/', '', (string) ($data['phone'] ?? '')) ?? '';

        Attendee::create([
            'meeting_id' => $meeting->id,
            'meeting_company_id' => $attendee->meeting_company_id,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'department' => $data['department'] ?? null,
            'phone' => $digits === '' ? '0000000000' : substr($digits, 0, 15),
            'invitation_status' => 'draft',
            'requires_approval' => true,
        ]);

        return back()->with('invitation_success', 'Colleague added. The organizer will review and issue their invitation.');
    }

    public function confirmed(Request $request, Attendee $attendee): View|RedirectResponse|Response
    {
        if ($redirect = $this->authorizeInvitee($request, $attendee)) {
            return $redirect;
        }

        $meeting = $this->meeting($attendee);

        if ($error = $this->guardMeeting($meeting, $attendee)) {
            return $error;
        }

        if ($attendee->invitationStatus() !== 'accepted') {
            return redirect()->route('invitations.show', ['attendee' => $attendee->invitation_token]);
        }

        return view('invitations.confirmed', $this->payload($attendee, $meeting));
    }

    public function lobby(Request $request, Attendee $attendee): View|RedirectResponse|Response
    {
        if ($redirect = $this->authorizeInvitee($request, $attendee)) {
            return $redirect;
        }

        $meeting = $this->meeting($attendee);

        if ($error = $this->guardMeeting($meeting, $attendee)) {
            return $error;
        }

        if ($attendee->invitationStatus() !== 'accepted') {
            return redirect()->route('invitations.show', ['attendee' => $attendee->invitation_token]);
        }

        if ($meeting->inviteeJoinState() !== 'open') {
            return redirect()->route('invitations.confirmed', ['attendee' => $attendee->invitation_token]);
        }

        return view('invitations.lobby', $this->payload($attendee, $meeting));
    }

    public function enter(Request $request, Attendee $attendee): RedirectResponse
    {
        if ($redirect = $this->authorizeInvitee($request, $attendee)) {
            return $redirect;
        }

        $meeting = $this->meeting($attendee);

        abort_unless($attendee->invitationStatus() === 'accepted', 403, 'Accept the invitation before joining.');
        abort_if($meeting->isCancelled() || $meeting->ended_at !== null, 410, 'This meeting is no longer available.');
        abort_unless($meeting->is_online, 404);
        abort_unless($meeting->inviteeJoinState() === 'open', 409, 'The meeting has not started yet.');

        $url = URL::signedRoute('sessions.join', [
            'meeting' => $meeting,
            'invite' => $attendee->invitation_token,
        ]);

        return redirect()->away($url);
    }

    public function state(Request $request, Attendee $attendee): JsonResponse
    {
        if ($redirect = $this->authorizeInvitee($request, $attendee)) {
            return response()->json(['state' => 'unauthorized'], 403);
        }

        $meeting = $this->meeting($attendee);

        return response()->json([
            'state' => $meeting->inviteeJoinState(),
            'status' => $meeting->dashboardStatus(),
            'accepted' => $attendee->invitationStatus() === 'accepted',
        ]);
    }

    /**
     * Registered invitees must sign in and match the invited email; external
     * invitees are verified by the secret token that was emailed to them.
     */
    private function authorizeInvitee(Request $request, Attendee $attendee): ?RedirectResponse
    {
        $registered = User::where('email', $attendee->email)->first();

        if (! $registered) {
            return null;
        }

        if (! auth()->check()) {
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('login')->with('status', 'Sign in to respond to your AykonMeet invitation.');
        }

        if (strtolower(auth()->user()->email) !== strtolower($attendee->email)) {
            abort(403, 'This invitation belongs to another account.');
        }

        return null;
    }

    private function guardMeeting(Meeting $meeting, Attendee $attendee): ?Response
    {
        if ($meeting->isCancelled()) {
            return $this->errorView(
                'Invitation cancelled',
                'This meeting has been cancelled by the organizer, so this invitation is no longer valid.',
                410,
            );
        }

        if ($meeting->ended_at !== null || in_array($meeting->status, ['completed'], true)) {
            return $this->errorView(
                'Invitation expired',
                'This meeting has already ended, so this invitation is no longer valid.',
                410,
            );
        }

        if ($attendee->invitation_expires_at && $attendee->invitation_expires_at->isPast()) {
            return $this->errorView(
                'Invitation expired',
                'This invitation link has expired. Please contact the organizer to request a new one.',
                410,
            );
        }

        return null;
    }

    private function errorView(string $title, string $message, int $status): Response
    {
        return response()->view('invitations.error', compact('title', 'message'), $status);
    }

    private function meeting(Attendee $attendee): Meeting
    {
        /** @var Meeting $meeting */
        $meeting = $attendee->meeting()
            ->with([
                'room',
                'companies' => fn ($query) => $query
                    ->orderByDesc('is_host_company')
                    ->orderBy('name'),
            ])
            ->firstOrFail();

        return $meeting;
    }

    /**
     * Shared, privacy-safe data for the invitee views. Exposes only company
     * names/logos and never organizer-only settings or company contact details.
     *
     * @return array<string, mixed>
     */
    private function payload(Attendee $attendee, Meeting $meeting): array
    {
        $attendee->loadMissing('meetingCompany');

        $companies = $meeting->companies->map(fn ($company) => [
            'id' => $company->id,
            'name' => $company->name,
            'logo' => $company->logo_path,
            'is_host' => (bool) $company->is_host_company,
            'is_mine' => $company->id === $attendee->meeting_company_id,
        ])->values();

        $durationLabel = Meeting::durationLabel($meeting->start_date, $meeting->end_date);

        return [
            'attendee' => $attendee,
            'meeting' => $meeting,
            'companies' => $companies,
            'myCompany' => $attendee->meetingCompany,
            'invitingCompany' => $attendee->invitingCompanyName(),
            'status' => $attendee->invitationStatus(),
            'statusLabel' => $attendee->invitationStatusLabel(),
            'joinState' => $meeting->inviteeJoinState(),
            'durationLabel' => $durationLabel,
            'invitationUrl' => $attendee->invitationUrl(),
            'canInviteColleague' => $attendee->invitationStatus() === 'accepted'
                && $attendee->meetingCompany !== null
                && ! $attendee->meetingCompany->is_host_company
                && (bool) $meeting->setting('company_self_invite'),
            'verified' => $attendee->invitation_verified_at !== null,
        ];
    }
}
