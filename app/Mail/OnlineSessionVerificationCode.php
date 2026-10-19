<?php

namespace App\Mail;

use App\Models\Attendee;
use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OnlineSessionVerificationCode extends Mailable
{
    use Queueable;

    public function __construct(
        public Meeting $meeting,
        public Attendee $attendee,
        public string $code,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your AykonMeet verification code');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.online-session-verification-code');
    }
}
