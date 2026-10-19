<?php

namespace App\Mail;

use App\Models\Attendee;
use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InvitationResponse extends Mailable
{
    use Queueable;

    public function __construct(
        public Meeting $meeting,
        public Attendee $attendee,
        public string $response,
    ) {}

    public function envelope(): Envelope
    {
        $verb = $this->response === 'accepted' ? 'accepted' : 'declined';

        return new Envelope(
            subject: $this->attendee->full_name.' '.$verb.' the invitation: '.$this->meeting->meeting_topic,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation-response',
        );
    }
}
