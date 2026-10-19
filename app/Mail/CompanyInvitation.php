<?php

namespace App\Mail;

use App\Models\Meeting;
use App\Models\MeetingCompany;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CompanyInvitation extends Mailable
{
    use Queueable;

    public string $invitationUrl;

    public string $durationLabel;

    public function __construct(
        public Meeting $meeting,
        public MeetingCompany $company,
    ) {
        $this->invitationUrl = $company->invitationUrl() ?? route('landing');
        $this->durationLabel = Meeting::durationLabel($meeting->start_date, $meeting->end_date);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your team invitation link: '.$this->meeting->meeting_topic,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.company-invitation',
        );
    }
}
