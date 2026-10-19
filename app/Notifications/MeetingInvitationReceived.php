<?php

namespace App\Notifications;

use App\Models\Attendee;
use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MeetingInvitationReceived extends Notification
{
    use Queueable;

    public function __construct(
        public Meeting $meeting,
        public Attendee $attendee,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'invitation_received',
            'title' => 'New meeting invitation',
            'message' => $this->meeting->name.' invited '.$this->attendee->companyName().' to "'.$this->meeting->meeting_topic.'".',
            'meeting_id' => $this->meeting->id,
            'attendee_id' => $this->attendee->id,
            'url' => $this->attendee->invitationUrl(),
            'date' => now()->toIso8601String(),
        ];
    }
}
