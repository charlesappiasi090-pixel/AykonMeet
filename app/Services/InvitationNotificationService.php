<?php

namespace App\Services;

use App\Mail\InvitationResponse;
use App\Mail\MeetingRescheduled;
use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\User;
use App\Notifications\InvitationResponseReceived;
use App\Notifications\MeetingInvitationReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvitationNotificationService
{
    /**
     * Deliver an in-app notification to a registered recipient, if one exists.
     */
    public function recipientInvited(Meeting $meeting, Attendee $attendee): void
    {
        User::where('email', $attendee->email)
            ->get()
            ->each(fn (User $user) => $user->notify(new MeetingInvitationReceived($meeting, $attendee)));
    }

    /**
     * Tell the organizer (in-app to admins, by email to the meeting contact)
     * that a representative responded to an invitation.
     */
    public function organizerResponded(Meeting $meeting, Attendee $attendee, string $response): void
    {
        User::where('is_admin', true)
            ->get()
            ->each(fn (User $user) => $user->notify(new InvitationResponseReceived($meeting, $attendee, $response)));

        try {
            Mail::to($meeting->email)->send(new InvitationResponse($meeting, $attendee, $response));
        } catch (\Throwable $exception) {
            Log::warning('Failed to email invitation response to '.$meeting->email.': '.$exception->getMessage());
        }
    }

    /**
     * Email every non-declined attendee that the meeting has been rescheduled.
     */
    public function meetingRescheduled(Meeting $meeting): int
    {
        $sent = 0;

        foreach ($meeting->attendees as $attendee) {
            if ($attendee->invitationStatus() === 'declined') {
                continue;
            }

            try {
                Mail::to($attendee->email)->send(new MeetingRescheduled($meeting, $attendee));
                $sent++;
            } catch (\Throwable $exception) {
                Log::warning('Failed to send reschedule notice to '.$attendee->email.': '.$exception->getMessage());
            }
        }

        return $sent;
    }
}
