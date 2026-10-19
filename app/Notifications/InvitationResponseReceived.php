<?php

namespace App\Notifications;

use App\Models\Attendee;
use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvitationResponseReceived extends Notification
{
    use Queueable;

    public function __construct(
        public Meeting $meeting,
        public Attendee $attendee,
        public string $response,
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
            'kind' => 'invitation_response',
            'response' => $this->response,
            'title' => 'Invitation '.$this->response,
            'message' => $this->attendee->full_name.' '.$this->response.' the invitation to "'.$this->meeting->meeting_topic.'".',
            'meeting_id' => $this->meeting->id,
            'attendee_id' => $this->attendee->id,
            'url' => route('meetings.details', $this->meeting),
            'date' => now()->toIso8601String(),
        ];
    }
}
