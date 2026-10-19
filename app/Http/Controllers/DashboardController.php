<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $inbox = $user->notifications()
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($notification): array {
                $data = $notification->data;

                return [
                    'id' => $notification->id,
                    'title' => $data['title'] ?? 'Notification',
                    'message' => $data['message'] ?? '',
                    'date' => isset($data['date']) ? Carbon::parse($data['date']) : $notification->created_at,
                    'url' => $data['url'] ?? null,
                    'kind' => $data['kind'] ?? 'inbox',
                    'response' => $data['response'] ?? null,
                    'read' => $notification->read_at !== null,
                ];
            })
            ->values();

        $unreadCount = $user->unreadNotifications()->count();
        $user->unreadNotifications->markAsRead();

        $invitations = Attendee::with('meeting')
            ->where('email', $user->email)
            ->latest()
            ->get()
            ->map(fn (Attendee $attendee): array => [
                'attendee' => $attendee,
                'meeting' => $attendee->meeting,
                'state' => $attendee->invitationState(),
                'label' => $attendee->invitationStateLabel(),
                'tone' => $attendee->invitationStateTone(),
                'url' => $attendee->invitationUrl(),
            ]);

        return view('dashboard.index', compact('user', 'inbox', 'unreadCount', 'invitations'));
    }
}
