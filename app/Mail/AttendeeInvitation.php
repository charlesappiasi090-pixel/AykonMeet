<?php

namespace App\Mail;

use App\Models\Attendee;
use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

class AttendeeInvitation extends Mailable
{
    // Removed Queueable trait to ensure synchronous sending in tests

    public ?string $joinUrl;

    public ?string $rsvpUrl;

    public string $invitationUrl;

    public string $durationLabel;

    public function __construct(
        public Meeting $meeting,
        public Attendee $attendee,
    ) {
        $this->joinUrl = $meeting->is_online
            ? URL::signedRoute('sessions.join', ['meeting' => $meeting, 'invite' => $attendee->invitation_token])
            : null;
        $this->rsvpUrl = $meeting->isHybrid()
            ? route('meetings.show', $meeting->meeting_code)
            : null;
        $this->invitationUrl = $attendee->invitationUrl();
        $this->durationLabel = Meeting::durationLabel($meeting->start_date, $meeting->end_date);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You are invited: '.$this->meeting->meeting_topic,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.attendee-invitation',
        );
    }
}
