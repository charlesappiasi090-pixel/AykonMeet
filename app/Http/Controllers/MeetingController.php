<?php

namespace App\Http\Controllers;

use App\Mail\AttendeeInvitation;
use App\Mail\MeetingCancelled;
use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\MeetingRoom;
use App\Services\AttendeeImporter;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MeetingController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        return view('meetings.index', [
            'meetings' => Meeting::with('room')->withCount('attendees')->latest('start_date')->get(),
            'rooms' => MeetingRoom::orderBy('name')->get(),
            'stats' => [
                'total_meetings' => Meeting::count(),
                'today_meetings' => Meeting::whereDate('start_date', $today)->count(),
                'live_meetings' => Meeting::where('status', 'in_progress')->count(),
                'checked_in' => Attendee::whereNotNull('checked_in_at')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:40'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'meeting_topic' => ['required', 'string', 'max:180'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'from_location' => ['nullable', 'string', 'max:160'],
            'to_location' => ['nullable', 'string', 'max:160'],
            'meeting_room_id' => ['nullable', 'exists:meeting_rooms,id'],
        ]);

        if ($this->scheduleConflicts($data['start_date'], $data['end_date'], null, $data['meeting_room_id'] ?? null)) {
            return back()->withInput()->withErrors([
                'start_date' => 'This time overlaps another booked meeting. Choose a free date and time.',
            ]);
        }

        Meeting::create($data);

        return back()->with('success', 'Meeting scheduled and code generated.');
    }

    public function storeRoom(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'number' => ['required', 'string', 'max:30'],
            'floor' => ['required', 'string', 'max:30'],
        ]);

        MeetingRoom::create($data);

        return back()->with('success', 'Meeting room added.');
    }

    public function show(string $code)
    {
        $meeting = Meeting::with(['room', 'attendees'])->where('meeting_code', strtoupper($code))->first();

        if (! $meeting) {
            return redirect()->route('landing')
                ->withInput(['code' => strtoupper($code)])
                ->with('error', 'We could not find a meeting with that code. Check it and try again.');
        }

        return view('meetings.show', compact('meeting'));
    }

    public function editReschedule(Meeting $meeting)
    {
        return view('meetings.reschedule', compact('meeting'));
    }

    public function edit(Meeting $meeting)
    {
        $rooms = MeetingRoom::orderBy('name')->get();

        return view('meetings.edit', compact('meeting', 'rooms'));
    }

    public function update(Request $request, Meeting $meeting)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:40'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'meeting_topic' => ['required', 'string', 'max:180'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'from_location' => ['nullable', 'string', 'max:160'],
            'to_location' => ['nullable', 'string', 'max:160'],
            'meeting_room_id' => ['nullable', 'exists:meeting_rooms,id'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($this->scheduleConflicts($data['start_date'], $data['end_date'], $meeting->id, $data['meeting_room_id'] ?? null)) {
            return back()->withInput()->withErrors([
                'start_date' => 'This time overlaps another booked meeting. Choose a free date and time.',
            ]);
        }

        $meeting->update(array_merge($data, [
            'cancelled_at' => null,
            'status' => $meeting->isCancelled() ? 'scheduled' : $meeting->status,
        ]));

        return redirect()->route('meetings.attendance', $meeting)
            ->with('success', 'Meeting details updated.');
    }

    public function attendance(Meeting $meeting)
    {
        $meeting->load(['room', 'attendees']);

        return view('meetings.attendance', compact('meeting'));
    }

    public function badges(Meeting $meeting)
    {
        $meeting->load(['room', 'attendees']);

        $checkInUrl = route('kiosk.show', $meeting->meeting_code);
        app(QrCodeService::class)->hydrateAttendees($meeting->attendees, $checkInUrl);

        return view('meetings.badges', compact('meeting'));
    }

    public function kiosk(string $code)
    {
        $meeting = Meeting::with(['room', 'attendees'])->where('meeting_code', strtoupper($code))->first();

        if (! $meeting) {
            return redirect()->route('landing')->with('error', 'We could not find a meeting with that code.');
        }

        return view('meetings.kiosk', compact('meeting'));
    }

    public function start(Meeting $meeting)
    {
        if ($meeting->ended_at) {
            return back()->with('error', 'This meeting has already ended.');
        }

        if ($meeting->isCancelled()) {
            return back()->with('error', 'A cancelled meeting cannot be started. Reopen it first.');
        }

        $meeting->update(['started_at' => $meeting->started_at ?? now(), 'status' => 'in_progress']);

        return redirect()
            ->route('meetings.show', $meeting->meeting_code)
            ->with('success', 'Meeting started.');
    }

    public function end(Meeting $meeting)
    {
        if ($meeting->ended_at) {
            return back()->with('error', 'This meeting has already ended.');
        }

        if ($meeting->isCancelled()) {
            return back()->with('error', 'A cancelled meeting cannot be ended.');
        }

        $meeting->update([
            'started_at' => $meeting->started_at ?? now(),
            'ended_at' => now(),
            'status' => 'completed',
        ]);

        return back()->with('success', 'Meeting ended.');
    }

    public function cancel(Meeting $meeting)
    {
        if ($meeting->isCancelled()) {
            return back()->with('error', 'This meeting is already cancelled.');
        }

        if ($meeting->status === 'completed') {
            return back()->with('error', 'A completed meeting cannot be cancelled.');
        }

        $meeting->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'started_at' => null,
            'ended_at' => null,
        ]);

        $notified = 0;
        foreach ($meeting->attendees as $attendee) {
            if ($this->sendCancellation($meeting, $attendee)) {
                $notified++;
            }
        }

        $message = 'Meeting cancelled.';
        if ($notified > 0) {
            $message .= ' '.$notified.' attendee(s) notified by email.';
        } elseif ($meeting->attendees->isNotEmpty()) {
            $message .= ' Email notifications could not be delivered to all attendees.';
        }

        return back()->with('success', $message);
    }

    public function reopen(Meeting $meeting)
    {
        if (! $meeting->isCancelled()) {
            return back()->with('error', 'Only cancelled meetings can be reopened.');
        }

        $meeting->update([
            'status' => 'scheduled',
            'cancelled_at' => null,
        ]);

        return back()->with('success', 'Meeting reopened. It is scheduled again.');
    }

    public function saveNotes(Request $request, Meeting $meeting)
    {
        $data = $request->validate(['notes' => ['nullable', 'string']]);

        $meeting->update(['notes' => $data['notes']]);

        return back()->with('success', 'Meeting notes saved.');
    }

    public function exportPdf(Meeting $meeting)
    {
        if (! $meeting->ended_at) {
            return back()->with('error', 'The meeting must be ended before it can be exported.');
        }

        if ($meeting->isCancelled()) {
            return back()->with('error', 'A cancelled meeting cannot be exported.');
        }

        $meeting->load(['room', 'attendees']);

        return Pdf::loadView('meetings.pdf', compact('meeting'))
            ->download('meeting-'.$meeting->meeting_code.'.pdf');
    }

    public function reschedule(Request $request, Meeting $meeting)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        if ($this->scheduleConflicts($data['start_date'], $data['end_date'], $meeting->id, $meeting->meeting_room_id)) {
            return back()->withInput()->withErrors([
                'start_date' => 'This time overlaps another booked meeting. Choose a free date and time.',
            ]);
        }

        $meeting->update([
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'started_at' => null,
            'ended_at' => null,
            'status' => 'scheduled',
        ]);

        return back()->with('success', 'Meeting rescheduled successfully.');
    }

    private function scheduleConflicts(string $startDate, string $endDate, ?int $ignoreMeetingId = null, ?int $meetingRoomId = null): bool
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        return Meeting::query()
            ->when($ignoreMeetingId, fn ($query) => $query->whereKeyNot($ignoreMeetingId))
            ->when($meetingRoomId, fn ($query) => $query->where('meeting_room_id', $meetingRoomId), fn ($query) => $query->whereNull('meeting_room_id'))
            ->where('start_date', '<', $end)
            ->where('end_date', '>', $start)
            ->exists();
    }

    public function checkIn(Request $request, Meeting $meeting)
    {
        $request->validate(['attendee_code' => ['required', 'string', 'max:40']]);
        $attendee = $meeting->attendees()->where('attendee_code', strtoupper($request->attendee_code))->first();

        if (! $attendee) {
            return back()->with('unknown_attendee', true)->with('unknown_attendee_code', strtoupper($request->attendee_code))->withInput();
        }

        $attendee->update(['checked_in_at' => $attendee->checked_in_at ?? now()]);

        return back()->with('success', $attendee->full_name.' marked present.');
    }

    public function kioskCheckIn(Request $request, Meeting $meeting)
    {
        $request->validate(['attendee_code' => ['required', 'string', 'max:40']]);
        $attendee = $meeting->attendees()->where('attendee_code', strtoupper($request->attendee_code))->first();

        if (! $attendee) {
            return redirect()
                ->route('kiosk.show', $meeting->meeting_code)
                ->with('kiosk_error', strtoupper($request->attendee_code));
        }

        $attendee->update(['checked_in_at' => $attendee->checked_in_at ?? now()]);

        return redirect()
            ->route('kiosk.show', $meeting->meeting_code)
            ->with('kiosk_success', $attendee->full_name);
    }

    public function undoCheckIn(Attendee $attendee)
    {
        $attendee->update(['checked_in_at' => null, 'checked_out_at' => null]);

        return back()->with('success', $attendee->full_name.' marked as expected.');
    }

    public function toggleCheckOut(Attendee $attendee)
    {
        if (! $attendee->checked_in_at) {
            return back()->with('error', $attendee->full_name.' has not checked in yet.');
        }

        $attendee->update(['checked_out_at' => $attendee->checked_out_at ? null : now()]);

        $state = $attendee->checked_out_at ? 'checked out' : 'checked back in';

        return back()->with('success', $attendee->full_name.' '.$state.'.');
    }

    public function destroyAttendee(Attendee $attendee)
    {
        $attendee->delete();

        return back()->with('success', $attendee->full_name.' removed from the meeting.');
    }

    public function addAttendee(Request $request, Meeting $meeting)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:40'],
        ]);
        $data['checked_in_at'] = $request->has('mark_present')
            ? ($request->boolean('mark_present') ? now() : null)
            : now();
        $attendee = $meeting->attendees()->create($data);

        $this->sendInvitation($meeting, $attendee);

        return back()->with('success', 'Attendee added, marked present and '.$attendee->email.' emailed their code.');
    }

    public function importAttendees(Request $request, Meeting $meeting)
    {
        $request->validate(['attendees_file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120']]);

        $result = app(AttendeeImporter::class)->import($request->file('attendees_file'), $meeting);

        if ($result['created'] === 0) {
            return back()->with('error', $result['error'] ?? 'No valid rows were imported.');
        }

        $sent = 0;
        foreach ($result['attendees'] as $attendee) {
            if ($this->sendInvitation($meeting, $attendee)) {
                $sent++;
            }
        }

        $message = $result['created'].' attendees imported, '.$sent.' emailed their codes.';
        if (($result['skipped'] ?? 0) > 0) {
            $message .= ' '.$result['skipped'].' row(s) skipped (missing/invalid/duplicate data).';
        }

        return back()->with('success', $message);
    }

    private function sendInvitation(Meeting $meeting, Attendee $attendee): bool
    {
        try {
            Mail::to($attendee->email)->send(new AttendeeInvitation($meeting, $attendee));

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Failed to send attendee invitation to '.$attendee->email.': '.$exception->getMessage());

            return false;
        }
    }

    private function sendCancellation(Meeting $meeting, Attendee $attendee): bool
    {
        try {
            Mail::to($attendee->email)->send(new MeetingCancelled($meeting, $attendee));

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Failed to send cancellation notice to '.$attendee->email.': '.$exception->getMessage());

            return false;
        }
    }

    public function downloadTemplate()
    {
        $filename = 'attendees-template.csv';
        $content = "Full Name,Email,Phone\n";

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
