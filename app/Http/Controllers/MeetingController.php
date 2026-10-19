<?php

namespace App\Http\Controllers;

use App\Mail\AttendeeInvitation;
use App\Mail\CompanyInvitation;
use App\Mail\InvitationReminder;
use App\Mail\MeetingCancelled;
use App\Models\Attendee;
use App\Models\DocumentShare;
use App\Models\Meeting;
use App\Models\MeetingCompany;
use App\Models\MeetingRoom;
use App\Models\MeetingSeries;
use App\Models\OnlineSessionParticipant;
use App\Models\OnlineSessionSetting;
use App\Models\ParticipantApproval;
use App\Services\AttendeeImporter;
use App\Services\AuditLogService;
use App\Services\InvitationNotificationService;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MeetingController extends Controller
{
    public function rooms()
    {
        return view('rooms.index', [
            'rooms' => MeetingRoom::withCount('meetings')->orderBy('name')->get(),
            'stats' => [
                'rooms' => MeetingRoom::count(),
                'today' => Meeting::whereDate('start_date', now()->toDateString())
                    ->where('status', '!=', 'cancelled')
                    ->count(),
                'upcoming' => Meeting::where('start_date', '>', now())
                    ->where('status', '!=', 'cancelled')
                    ->count(),
                'scheduled' => Meeting::where('status', '!=', 'cancelled')->count(),
            ],
        ]);
    }

    public function index()
    {
        $now = now();
        $today = $now->toDateString();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $todayAttendees = Attendee::with('meeting.room')
            ->whereHas('meeting', fn ($query) => $query
                ->whereDate('start_date', $today)
                ->where('status', '!=', 'cancelled'))
            ->get();
        $todayMeetings = Meeting::with('room')
            ->withCount('attendees')
            ->whereDate('start_date', $today)
            ->orderBy('start_date')
            ->get();
        $rooms = MeetingRoom::with(['meetings' => fn ($query) => $query
            ->where('status', 'in_progress')
            ->withCount('attendees')])
            ->orderBy('name')
            ->get();
        $occupiedRooms = $rooms->filter(fn (MeetingRoom $room) => $room->meetings->isNotEmpty())->count();
        $checkedInArrivals = $todayAttendees
            ->filter(fn (Attendee $attendee) => $attendee->checked_in_at !== null)
            ->sortByDesc('checked_in_at')
            ->take(6)
            ->values();
        $allLateArrivals = $todayAttendees
            ->filter(fn (Attendee $attendee) => $attendee->checked_in_at === null
                && $attendee->meeting->start_date->isPast())
            ->sortBy(fn (Attendee $attendee) => $attendee->meeting->start_date);
        $lateArrivals = $allLateArrivals
            ->take(6)
            ->values();
        $recentActivity = Attendee::with('meeting.room')
            ->whereNotNull('checked_in_at')
            ->whereBetween('checked_in_at', [$todayStart, $todayEnd])
            ->latest('checked_in_at')
            ->limit(6)
            ->get();
        $upcomingMeetings = Meeting::with('room')
            ->where('status', 'scheduled')
            ->whereBetween('start_date', [$now, $now->copy()->addHours(3)])
            ->orderBy('start_date')
            ->get();
        $overstayedMeetings = Meeting::with('room')
            ->where('status', 'in_progress')
            ->where('end_date', '<', $now)
            ->orderBy('end_date')
            ->get();
        $notCheckedOut = Attendee::with('meeting.room')
            ->whereNotNull('checked_in_at')
            ->whereNull('checked_out_at')
            ->whereHas('meeting', fn ($query) => $query->where('status', 'completed'))
            ->latest('checked_in_at')
            ->limit(6)
            ->get();
        $activeDevices = OnlineSessionParticipant::whereNull('left_at')
            ->where('last_seen_at', '>=', $now->copy()->subSeconds(90))
            ->whereHas('meeting', fn ($query) => $query->where('status', 'in_progress'))
            ->count();

        return view('meetings.index', [
            'rooms' => $rooms,
            'todayMeetings' => $todayMeetings,
            'recentActivity' => $recentActivity,
            'checkedInArrivals' => $checkedInArrivals,
            'lateArrivals' => $lateArrivals,
            'upcomingMeetings' => $upcomingMeetings,
            'overstayedMeetings' => $overstayedMeetings,
            'notCheckedOut' => $notCheckedOut,
            'occupiedRooms' => $occupiedRooms,
            'activeDevices' => $activeDevices,
            'nextMeeting' => $todayMeetings->first(fn (Meeting $meeting) => $meeting->start_date->isFuture()),
            'stats' => [
                'total_meetings' => Meeting::count(),
                'total_trend' => Meeting::whereDate('created_at', $today)->count()
                    - Meeting::whereDate('created_at', $now->copy()->subDay()->toDateString())->count(),
                'today_meetings' => $todayMeetings->count(),
                'live_meetings' => Meeting::where('status', 'in_progress')->count(),
                'checked_in' => $todayAttendees->filter(fn (Attendee $attendee) => $attendee->checked_in_at !== null)->count(),
                'pending_check_in' => $todayAttendees->filter(fn (Attendee $attendee) => $attendee->checked_in_at === null)->count(),
                'late_arrivals' => $allLateArrivals->count(),
                'no_show_arrivals' => $allLateArrivals->filter(fn (Attendee $attendee) => $attendee->meeting->status === 'completed'
                    || $attendee->meeting->end_date->isPast())->count(),
                'room_occupancy_percent' => $rooms->isEmpty() ? 0 : (int) round($occupiedRooms / $rooms->count() * 100),
            ],
        ]);
    }

    public function create()
    {
        $rooms = MeetingRoom::orderBy('name')->get();

        return view('meetings.create', compact('rooms'));
    }

    public function createHybrid()
    {
        $rooms = MeetingRoom::orderBy('name')->get();

        // Clear any previous wizard session data
        session()->forget('hybrid_wizard');

        return view('meetings.hybrid-create.step1', compact('rooms'));
    }

    public function createHybridStep1(Request $request)
    {
        $rooms = MeetingRoom::orderBy('name')->get();

        $data = $request->validate([
            'meeting_topic' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'regex:/^[0-9]{1,10}$/'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'timezone' => ['required', 'string', 'max:50'],
            'recurrence_type' => ['nullable', 'in:none,daily,weekly,monthly,quarterly'],
            'recurrence_interval' => ['nullable', 'integer', 'min:1', 'max:52'],
            'recurrence_days_of_week' => ['nullable', 'array'],
            'recurrence_days_of_week.*' => ['integer', 'min:1', 'max:7'],
            'recurrence_day_of_month' => ['nullable', 'integer', 'min:1', 'max:28'],
            'recurrence_end_type' => ['nullable', 'in:never,on_date,after_occurrences'],
            'recurrence_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'recurrence_max_occurrences' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        // Combine date and time
        $data['start_date'] = $data['start_date'].'T'.$data['start_time'];
        $data['end_date'] = $data['start_date'].'T'.$data['end_time'];

        // Store in session
        session(['hybrid_wizard.step1' => $data]);

        return redirect()->route('meetings.create.hybrid.step2');
    }

    public function createHybridStep2()
    {
        $rooms = MeetingRoom::orderBy('name')->get();

        if (! session()->has('hybrid_wizard.step1')) {
            return redirect()->route('meetings.create.hybrid');
        }

        return view('meetings.hybrid-create.step2', compact('rooms'));
    }

    public function createHybridStep2Store(Request $request)
    {
        $data = $request->validate([
            'venue_name' => ['required', 'string', 'max:255'],
            'venue_address' => ['required', 'string'],
            'venue_building_room' => ['nullable', 'string', 'max:255'],
            'venue_instructions' => ['nullable', 'string'],
            'venue_capacity' => ['nullable', 'integer', 'min:1'],
            'meeting_room_id' => ['required', 'exists:meeting_rooms,id'],
            'waiting_room' => ['nullable', 'boolean'],
            'waiting_room_message' => ['nullable', 'string', 'max:500'],
            'mute_on_entry' => ['nullable', 'boolean'],
            'allow_participant_unmute' => ['nullable', 'boolean'],
            'allow_participant_camera' => ['nullable', 'boolean'],
            'allow_screen_share' => ['nullable', 'boolean'],
            'allow_chat' => ['nullable', 'boolean'],
            'allow_recording' => ['nullable', 'boolean'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'reminder_before_minutes' => ['nullable', 'integer', 'min:5'],
            'reminder_before_hours' => ['nullable', 'integer', 'min:1'],
            'reminder_before_days' => ['nullable', 'integer', 'min:1'],
            'co_host' => ['nullable', 'string', 'max:160'],
            'co_host_permissions' => ['nullable', 'array'],
            'co_host_permissions.*' => ['string', Rule::in(array_keys(OnlineSessionParticipant::CO_HOST_PERMISSIONS))],
        ]);

        session(['hybrid_wizard.step2' => $data]);

        return redirect()->route('meetings.create.hybrid.step3');
    }

    public function createHybridStep3()
    {
        if (! session()->has('hybrid_wizard.step1') || ! session()->has('hybrid_wizard.step2')) {
            return redirect()->route('meetings.create.hybrid');
        }

        return view('meetings.hybrid-create.step3');
    }

    public function createHybridStep3Store(Request $request)
    {
        $data = $request->validate([
            'require_registration' => ['nullable', 'boolean'],
            'allow_rsvp_maybe' => ['nullable', 'boolean'],
            'allow_rsvp_another_time' => ['nullable', 'boolean'],
            'enable_document_sharing' => ['nullable', 'boolean'],
            'allow_participant_upload' => ['nullable', 'boolean'],
            'attendees' => ['nullable', 'array'],
            'attendees.*.full_name' => ['required_with:attendees', 'string', 'max:160'],
            'attendees.*.email' => ['required_with:attendees', 'email', 'max:160'],
        ]);

        session(['hybrid_wizard.step3' => $data]);

        // Merge all steps and create the meeting
        return $this->storeHybridFromWizard($request);
    }

    public function createHybridBack(Request $request)
    {
        $step = $request->input('step', 1);

        if ($step == 2) {
            return redirect()->route('meetings.create.hybrid');
        } elseif ($step == 3) {
            return redirect()->route('meetings.create.hybrid.step2');
        }

        return redirect()->route('meetings.create.hybrid');
    }

    private function storeHybridFromWizard(Request $request)
    {
        $step1 = session('hybrid_wizard.step1');
        $step2 = session('hybrid_wizard.step2');
        $step3 = session('hybrid_wizard.step3');

        // Merge all data
        $data = array_merge($step1, $step2, $step3);

        // Extract hybrid fields
        $hybridFields = [
            'venue_name' => $step2['venue_name'] ?? null,
            'venue_address' => $step2['venue_address'] ?? null,
            'venue_building_room' => $step2['venue_building_room'] ?? null,
            'venue_instructions' => $step2['venue_instructions'] ?? null,
            'venue_capacity' => $step2['venue_capacity'] ?? null,
            'timezone' => $step1['timezone'] ?? 'UTC',
            'require_registration' => $request->boolean('require_registration'),
            'allow_rsvp_maybe' => $request->boolean('allow_rsvp_maybe'),
            'allow_rsvp_another_time' => $request->boolean('allow_rsvp_another_time'),
            'enable_document_sharing' => $request->boolean('enable_document_sharing'),
            'allow_participant_upload' => $request->boolean('allow_participant_upload'),
        ];

        // Extract online settings
        $onlineSettings = [];
        $settingsFields = [
            'waiting_room', 'waiting_room_message', 'mute_on_entry',
            'allow_participant_unmute', 'allow_participant_camera',
            'allow_screen_share', 'allow_chat', 'allow_recording',
            'max_participants', 'reminder_before_minutes',
            'reminder_before_hours', 'reminder_before_days',
            'co_host', 'co_host_permissions',
        ];
        foreach ($settingsFields as $field) {
            if (isset($step2[$field])) {
                $onlineSettings[$field] = $step2[$field];
            }
        }

        // Handle recurrence
        $isRecurring = $step1['recurrence_type'] ?? 'none';
        $recurrenceData = null;
        if ($isRecurring !== 'none') {
            $recurrenceData = [
                'frequency' => $isRecurring,
                'interval' => $step1['recurrence_interval'] ?? 1,
                'days_of_week' => $step1['recurrence_days_of_week'] ?? null,
                'day_of_month' => $step1['recurrence_day_of_month'] ?? null,
                'recurrence_end_type' => $step1['recurrence_end_type'] ?? 'never',
                'recurrence_end_date' => $step1['recurrence_end_date'] ?? null,
                'recurrence_max_occurrences' => $step1['recurrence_max_occurrences'] ?? null,
            ];
        }

        $meetingType = 'hybrid';
        $isOnline = true;
        $isMultiCompany = false;

        $meeting = Meeting::create(array_merge($step1, $hybridFields, [
            'is_online' => $isOnline,
            'is_multi_company' => $isMultiCompany,
            'meeting_type' => $meetingType,
            'host_user_id' => $request->user()->id,
            'session_provider' => 'internal',
        ]));

        if (! empty($onlineSettings)) {
            $meeting->update(['settings' => $onlineSettings]);
        }

        // Create meeting series if recurring
        if ($isRecurring !== 'none' && $recurrenceData) {
            $series = $this->createMeetingSeries($meeting, $recurrenceData, $request->user());
            $meeting->update([
                'is_series_parent' => true,
                'series_id' => $series->id,
            ]);
        }

        // Process attendees
        $attendees = $step3['attendees'] ?? [];
        foreach ($attendees as $attendeeData) {
            $fullName = trim($attendeeData['full_name'] ?? '');
            $email = trim($attendeeData['email'] ?? '');

            if ($fullName === '' || $email === '') {
                continue;
            }

            $existingAttendee = $meeting->attendees()->where('email', $email)->first();

            if ($existingAttendee) {
                $existingAttendee->update([
                    'full_name' => $fullName,
                    'invitation_status' => 'draft',
                ]);
                $attendee = $existingAttendee;
            } else {
                $attendee = $meeting->attendees()->create([
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => '0000000000',
                    'invitation_status' => 'draft',
                    'invitation_expires_at' => $meeting->end_date,
                ]);
            }

            $this->sendInvitation($meeting, $attendee);
        }

        if ($isOnline) {
            $meeting->update([
                'session_url' => URL::signedRoute('sessions.join', ['meeting' => $meeting]),
            ]);
        }

        // Clear wizard session
        session()->forget('hybrid_wizard');

        return redirect()->route('meetings.details', $meeting)
            ->with('success', 'Hybrid meeting created successfully!')
            ->with('meeting_created', true);
    }

    public function liveMeetings()
    {
        $meetings = Meeting::with('room')
            ->withCount('attendees')
            ->where('status', 'in_progress')
            ->orderBy('start_date')
            ->get();

        return view('meetings.live', compact('meetings'));
    }

    public function checkIns()
    {
        $todayAttendees = Attendee::with('meeting.room')
            ->whereHas('meeting', fn ($query) => $query
                ->whereDate('start_date', now()->toDateString())
                ->where('status', '!=', 'cancelled'))
            ->get();
        $checkedInAttendees = $todayAttendees
            ->filter(fn (Attendee $attendee) => $attendee->checked_in_at !== null)
            ->sortByDesc('checked_in_at')
            ->values();
        $lateArrivals = $todayAttendees
            ->filter(fn (Attendee $attendee) => $attendee->checked_in_at === null
                && $attendee->meeting->start_date->isPast())
            ->sortBy(fn (Attendee $attendee) => $attendee->meeting->start_date)
            ->values();

        return view('meetings.check-ins', [
            'attendees' => $todayAttendees->sortBy(fn (Attendee $attendee) => $attendee->meeting->start_date)->values(),
            'checkedInAttendees' => $checkedInAttendees,
            'stats' => [
                'checked_in' => $checkedInAttendees->count(),
                'pending' => $todayAttendees->whereNull('checked_in_at')->count(),
                'late' => $lateArrivals->count() - $lateArrivals->filter(fn (Attendee $attendee) => $attendee->meeting->status === 'completed'
                    || $attendee->meeting->end_date->isPast())->count(),
                'no_show' => $lateArrivals->filter(fn (Attendee $attendee) => $attendee->meeting->status === 'completed'
                    || $attendee->meeting->end_date->isPast())->count(),
            ],
        ]);
    }

    public function allMeetings(Request $request)
    {
        $date = $request->validate([
            'date' => ['sometimes', 'date_format:Y-m-d'],
        ])['date'] ?? null;
        $query = Meeting::with('room')
            ->withCount('attendees')
            ->orderByDesc('start_date');

        if ($date !== null) {
            $query->whereDate('start_date', $date);
        }

        $meetings = $query->get();

        return view('meetings.all', [
            'meetings' => $meetings,
            'selectedDate' => $date,
            'isToday' => $date === now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $isHybridRequest = $request->input('meeting_type') === 'hybrid';
        $topicCategoryRules = $isHybridRequest
            ? ['nullable', 'in:Planning,Team meeting,Training,Workshop,Interview,Other,Custom']
            : ['nullable', 'in:Planning,Team meeting,Training,Workshop,Interview,Other'];
        $data = $request->validate([
            'meeting_type' => ['sometimes', 'required', 'in:online,in_person,hybrid'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'regex:/^[0-9]{1,10}$/'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'company_contact_email' => ['nullable', 'email', 'max:160'],
            'meeting_topic' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'topic_category' => $topicCategoryRules,
            'custom_topic' => [$isHybridRequest ? 'required_if:topic_category,Custom' : 'prohibited', 'nullable', 'string', 'max:80'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'from_location' => ['nullable', 'string', 'max:160'],
            'to_location' => ['nullable', 'string', 'max:160'],
            'physical_location' => ['nullable', 'string', 'max:255'],
            'meeting_room_id' => [
                $request->exists('meeting_type') && in_array($request->input('meeting_type'), ['in_person', 'hybrid'], true)
                    ? 'required'
                    : 'nullable',
                'exists:meeting_rooms,id',
            ],
            'companies' => ['nullable', 'array'],
            'companies.*' => ['nullable', 'string', 'max:160'],
            'departments' => ['nullable', 'array'],
            'departments.*' => ['nullable', 'string', 'max:160'],
            'company_emails' => ['nullable', 'array'],
            'company_emails.*' => ['nullable', 'email', 'max:160'],
            'company_phones' => ['nullable', 'array'],
            'company_phones.*' => ['nullable', 'string', 'max:40'],
            'attendees' => ['nullable', 'array'],
            'attendees.*.full_name' => ['required_with:attendees', 'string', 'max:160'],
            'attendees.*.email' => ['required_with:attendees', 'email', 'max:160'],
            // Recurrence fields
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_frequency' => ['nullable', 'in:daily,weekly,monthly,quarterly'],
            'recurrence_interval' => ['nullable', 'integer', 'min:1', 'max:52'],
            'recurrence_days_of_week' => ['nullable', 'array'],
            'recurrence_days_of_week.*' => ['integer', 'min:1', 'max:7'],
            'recurrence_day_of_month' => ['nullable', 'integer', 'min:1', 'max:28'],
            'recurrence_end_type' => ['nullable', 'in:never,on_date,after_occurrences'],
            'recurrence_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'recurrence_max_occurrences' => ['nullable', 'integer', 'min:1', 'max:1000'],
            // Hybrid meeting specific fields
            'venue_name' => ['nullable', 'string', 'max:255'],
            'venue_address' => ['nullable', 'string'],
            'venue_building_room' => ['nullable', 'string', 'max:255'],
            'venue_instructions' => ['nullable', 'string'],
            'venue_capacity' => ['nullable', 'integer', 'min:1'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'require_registration' => ['nullable', 'boolean'],
            'allow_rsvp_maybe' => ['nullable', 'boolean'],
            'allow_rsvp_another_time' => ['nullable', 'boolean'],
            'enable_document_sharing' => ['nullable', 'boolean'],
            'allow_participant_upload' => ['nullable', 'boolean'],
            // Online meeting settings (stored in settings JSON)
            'waiting_room' => ['nullable', 'boolean'],
            'waiting_room_message' => ['nullable', 'string', 'max:500'],
            'mute_on_entry' => ['nullable', 'boolean'],
            'allow_participant_unmute' => ['nullable', 'boolean'],
            'allow_participant_camera' => ['nullable', 'boolean'],
            'allow_screen_share' => ['nullable', 'boolean'],
            'allow_chat' => ['nullable', 'boolean'],
            'allow_recording' => ['nullable', 'boolean'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'reminder_before_minutes' => ['nullable', 'integer', 'min:5'],
            'reminder_before_hours' => ['nullable', 'integer', 'min:1'],
            'reminder_before_days' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($isHybridRequest && ($data['topic_category'] ?? null) === 'Custom') {
            $data['topic_category'] = $data['custom_topic'];
        }
        unset($data['custom_topic']);

        if ($this->scheduleConflicts($data['start_date'], $data['end_date'], null, $data['meeting_room_id'] ?? null)) {
            return back()->withInput()->withErrors([
                'start_date' => 'This time overlaps another booked meeting. Choose a free date and time.',
            ]);
        }

        $meetingType = $data['meeting_type'] ?? 'in_person';
        $isOnline = in_array($meetingType, ['online', 'hybrid'], true);
        $isMultiCompany = $isOnline && $request->boolean('is_multi_company');
        $invitedCompanies = $data['companies'] ?? [];
        $companyEmails = $data['company_emails'] ?? [];
        $companyPhones = $data['company_phones'] ?? [];
        $companyContactEmail = $data['company_contact_email'] ?? null;
        unset($data['meeting_type'], $data['companies'], $data['departments'], $data['company_contact_email'], $data['company_emails'], $data['company_phones']);

        // Extract hybrid meeting specific fields
        $hybridFields = [
            'venue_name' => $data['venue_name'] ?? null,
            'venue_address' => $data['venue_address'] ?? null,
            'venue_building_room' => $data['venue_building_room'] ?? null,
            'venue_instructions' => $data['venue_instructions'] ?? null,
            'venue_capacity' => $data['venue_capacity'] ?? null,
            'timezone' => $data['timezone'] ?? 'UTC',
            'require_registration' => $request->boolean('require_registration'),
            'allow_rsvp_maybe' => $request->boolean('allow_rsvp_maybe'),
            'allow_rsvp_another_time' => $request->boolean('allow_rsvp_another_time'),
            'enable_document_sharing' => $request->boolean('enable_document_sharing'),
            'allow_participant_upload' => $request->boolean('allow_participant_upload'),
        ];
        foreach (array_keys($hybridFields) as $key) {
            unset($data[$key]);
        }

        // Extract online meeting settings for settings JSON
        $onlineSettings = [];
        $settingsFields = [
            'waiting_room', 'waiting_room_message', 'mute_on_entry',
            'allow_participant_unmute', 'allow_participant_camera',
            'allow_screen_share', 'allow_chat', 'allow_recording',
            'max_participants', 'reminder_before_minutes',
            'reminder_before_hours', 'reminder_before_days',
        ];
        foreach ($settingsFields as $field) {
            if (isset($data[$field])) {
                $onlineSettings[$field] = $data[$field];
                unset($data[$field]);
            }
        }

        // Extract recurrence data before creating meeting
        $isRecurring = $request->boolean('is_recurring');
        $recurrenceData = null;
        if ($isRecurring) {
            $recurrenceData = [
                'frequency' => $request->input('recurrence_frequency'),
                'interval' => $request->input('recurrence_interval', 1),
                'days_of_week' => $request->input('recurrence_days_of_week'),
                'day_of_month' => $request->input('recurrence_day_of_month'),
                'recurrence_end_type' => $request->input('recurrence_end_type', 'never'),
                'recurrence_end_date' => $request->input('recurrence_end_date'),
                'recurrence_max_occurrences' => $request->input('recurrence_max_occurrences'),
            ];
            // Remove recurrence fields from data
            unset($data['is_recurring'], $data['recurrence_frequency'], $data['recurrence_interval'],
                $data['recurrence_days_of_week'], $data['recurrence_day_of_month'],
                $data['recurrence_end_type'], $data['recurrence_end_date'], $data['recurrence_max_occurrences']);
        }

        $meeting = Meeting::create(array_merge($data, $hybridFields, [
            'is_online' => $isOnline,
            'is_multi_company' => $isMultiCompany,
            'meeting_type' => $meetingType,
            'host_user_id' => $isOnline ? $request->user()->id : null,
            'session_provider' => $isOnline ? 'internal' : null,
        ]));

        // Update settings with online meeting settings
        if (! empty($onlineSettings)) {
            $currentSettings = $meeting->settings ?? [];
            $meeting->update(['settings' => array_merge($currentSettings, $onlineSettings)]);
        }

        // Create meeting series if recurring
        $series = null;
        if ($isRecurring && $recurrenceData) {
            $series = $this->createMeetingSeries($meeting, $recurrenceData, $request->user());
            $meeting->update([
                'is_series_parent' => true,
                'series_id' => $series->id,
            ]);
        }

        $invitationResults = [];

        if ($isMultiCompany) {
            if (! empty($data['company_name'])) {
                $hostCompany = MeetingCompany::forName($meeting, $data['company_name'], true);
            }
            foreach (array_filter(array_map('trim', $invitedCompanies)) as $index => $companyName) {
                $company = MeetingCompany::forName($meeting, $companyName);
                $email = $companyEmails[$index] ?? null;
                $phone = $companyPhones[$index] ?? null;

                if ($email) {
                    $company->update([
                        'contact_email' => $email,
                        'contact_phone' => $phone,
                    ]);
                }

                // Issue invitation token and send email
                $result = $this->issueAndSendCompanyInvitation($meeting, $company);
                $invitationResults[] = [
                    'company' => $company->name,
                    'email' => $email,
                    'success' => $result['success'],
                    'error' => $result['error'] ?? null,
                ];
            }
        } elseif ($isOnline && $companyContactEmail) {
            // Single company online meeting: create first company with contact email
            $company = MeetingCompany::forName($meeting, $data['company_name'] ?: 'Guests');
            $company->update(['contact_email' => $companyContactEmail]);

            // Issue invitation token and send email
            $result = $this->issueAndSendCompanyInvitation($meeting, $company);
            $invitationResults[] = [
                'company' => $company->name,
                'email' => $companyContactEmail,
                'success' => $result['success'],
                'error' => $result['error'] ?? null,
            ];
        }

        // Process individual attendees for single-company online meetings
        if ($isOnline && ! $isMultiCompany) {
            $attendees = $request->input('attendees', []);
            foreach ($attendees as $index => $attendeeData) {
                $fullName = trim($attendeeData['full_name'] ?? '');
                $email = trim($attendeeData['email'] ?? '');

                if ($fullName === '' || $email === '') {
                    continue;
                }

                // Check if attendee already exists for this meeting
                $existingAttendee = $meeting->attendees()->where('email', $email)->first();

                if ($existingAttendee) {
                    $existingAttendee->update([
                        'full_name' => $fullName,
                        'invitation_status' => 'draft',
                    ]);
                    $attendee = $existingAttendee;
                } else {
                    $attendee = $meeting->attendees()->create([
                        'full_name' => $fullName,
                        'email' => $email,
                        'phone' => '0000000000',
                        'invitation_status' => 'draft',
                        'invitation_expires_at' => $meeting->end_date,
                    ]);
                }

                // Send invitation email
                $sent = $this->sendInvitation($meeting, $attendee);
                $invitationResults[] = [
                    'company' => $attendee->full_name,
                    'email' => $email,
                    'success' => $sent,
                    'error' => $sent ? null : 'Failed to send invitation email',
                ];
            }
        }

        if ($isOnline) {
            $meeting->update([
                'session_url' => URL::signedRoute('sessions.join', ['meeting' => $meeting]),
            ]);
        }

        // Build redirect message with invitation results
        $message = 'Meeting Created Successfully.';
        if (! empty($invitationResults)) {
            $sentCount = collect($invitationResults)->where('success', true)->count();
            $failedCount = collect($invitationResults)->where('success', false)->count();
            $message .= " {$sentCount} invitation(s) sent.";
            if ($failedCount > 0) {
                $message .= " {$failedCount} failed — check the Invited Companies panel to retry.";
            }
        }

        $redirect = redirect()
            ->route('meetings.details', $meeting)
            ->with('success', $message)
            ->with('meeting_created', true)
            ->with('invitation_results', $invitationResults);

        if ($meetingType === 'hybrid') {
            $redirect->with('hybrid_share_url', route('meetings.show', $meeting->meeting_code));
        }

        return $redirect;
    }

    public function storeRoom(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'number' => ['required', 'string', 'max:30'],
            'floor' => ['required', 'string', 'max:30'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        MeetingRoom::create($data);

        return redirect()->route('admin.dashboard')->with('success', 'Meeting room added.');
    }

    public function show(string $code)
    {
        $meeting = Meeting::with(['room', 'attendees'])->where('meeting_code', strtoupper($code))->first();

        if (! $meeting) {
            return redirect()->route('landing')
                ->withInput(['code' => strtoupper($code)])
                ->with('error', 'We could not find a meeting with that code. Check it and try again.');
        }

        $canViewSessionReport = auth()->check()
            && $meeting->is_online
            && $meeting->host_user_id === auth()->id()
            && OnlineSessionSetting::current()->downloads_for_hosts;
        $hybridAttendance = $meeting->isHybrid() && auth()->check()
            && auth()->user()->isAdmin()
            ? app(OnlineSessionController::class)->hybridAttendanceData($meeting)
            : null;

        return view('meetings.show', compact('meeting', 'canViewSessionReport', 'hybridAttendance'));
    }

    public function details(Meeting $meeting)
    {
        $meeting->load([
            'room',
            'companies' => fn ($query) => $query
                ->with(['attendees' => fn ($attendee) => $attendee->orderBy('full_name')->orderBy('id')])
                ->orderByDesc('is_host_company')
                ->orderBy('name'),
            'attendees.meetingCompany',
        ]);

        $companies = $meeting->companies;
        $attendees = $meeting->attendees;

        $attendees->each->setRelation('meeting', $meeting);
        $companies->each->setRelation('meeting', $meeting);
        $companies->each(fn (MeetingCompany $company) => $company->attendees->each->setRelation('meeting', $meeting));

        $acceptedParticipants = $attendees->filter(fn (Attendee $attendee) => $attendee->checked_in_at !== null
            || $attendee->meetingCompany?->status === 'accepted')->count();

        $stats = [
            'companies' => $companies->count(),
            'expected' => $attendees->count(),
            'accepted' => $acceptedParticipants,
            'status' => $meeting->dashboardStatus(),
        ];

        $joinUrl = $meeting->joinUrl();
        $settings = $meeting->sessionSettings();
        $coHostCandidates = $attendees;

        return view('meetings.details', compact('meeting', 'companies', 'attendees', 'stats', 'joinUrl', 'settings', 'coHostCandidates'));
    }

    public function addCompany(Request $request, Meeting $meeting)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'contact_name' => ['nullable', 'string', 'max:160'],
            'contact_email' => ['nullable', 'email', 'max:160'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'rep_name' => ['nullable', 'string', 'max:160'],
            'rep_email' => ['nullable', 'email', 'max:160', 'required_with:rep_name'],
            'rep_phone' => ['nullable', 'string', 'max:40'],
            'rep_department' => ['nullable', 'string', 'max:160'],
        ]);

        $company = MeetingCompany::forName($meeting, $data['name']);
        $company->update(array_filter([
            'contact_name' => $data['contact_name'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
        ], fn ($value) => $value !== null && $value !== ''));

        $sent = false;
        if (! empty($data['rep_name']) && ! empty($data['rep_email'])) {
            $attendee = $meeting->attendees()->create([
                'full_name' => $data['rep_name'],
                'email' => $data['rep_email'],
                'phone' => $this->normalizePhone($data['rep_phone'] ?? null),
                'meeting_company_id' => $company->id,
                'department' => $data['rep_department'] ?? null,
            ]);
            $sent = $this->sendInvitation($meeting, $attendee);
            $company->update(['invited_at' => now()]);
        }

        $message = $data['name'].' added to the meeting.';
        if ($sent) {
            $message .= ' Invitation emailed to '.$data['rep_email'].'.';
        }

        return back()->with('success', $message);
    }

    public function updateCompany(Request $request, Meeting $meeting, MeetingCompany $company)
    {
        abort_unless($company->meeting_id === $meeting->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'contact_name' => ['nullable', 'string', 'max:160'],
            'contact_email' => ['nullable', 'email', 'max:160'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'status' => ['required', Rule::in(MeetingCompany::STATUSES)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $attributes = [
            'name' => $data['name'],
            'contact_name' => $data['contact_name'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ];

        $slug = Str::slug($data['name']) ?: 'company';
        $slugTaken = $meeting->companies()
            ->where('slug', $slug)
            ->whereKeyNot($company->id)
            ->exists();
        if (! $slugTaken) {
            $attributes['slug'] = $slug;
        }

        $company->update($attributes);

        return back()->with('success', $company->name.' updated.');
    }

    public function removeCompany(Meeting $meeting, MeetingCompany $company)
    {
        abort_unless($company->meeting_id === $meeting->id, 404);

        $name = $company->name;
        $company->delete();

        return back()->with('success', $name.' removed from the meeting.');
    }

    public function inviteCompany(Meeting $meeting, MeetingCompany $company)
    {
        abort_unless($company->meeting_id === $meeting->id, 404);

        if ($company->attendees()->count() === 0 && ! empty($company->contact_email)) {
            $company->attendees()->create([
                'full_name' => $company->contact_name ?: $company->name,
                'email' => $company->contact_email,
                'phone' => $this->normalizePhone($company->contact_phone),
                'meeting_company_id' => $company->id,
            ]);
        }

        $sent = 0;
        foreach ($company->attendees()->get() as $attendee) {
            if ($this->sendInvitation($meeting, $attendee)) {
                $sent++;
            }
        }

        $company->update(['invited_at' => now()]);

        if ($sent === 0) {
            return back()->with('error', 'No invitations could be sent for '.$company->name.' yet.');
        }

        return back()->with('success', $sent.' invitation(s) sent to '.$company->name.'.');
    }

    public function sendInvitations(Meeting $meeting)
    {
        \Log::info('sendInvitations called', ['meeting_id' => $meeting->id, 'attendees_count' => $meeting->attendees->count()]);

        $sent = 0;
        foreach ($meeting->attendees as $attendee) {
            \Log::info('Processing attendee', ['attendee_id' => $attendee->id, 'attendee_email' => $attendee->email]);
            if ($this->sendInvitation($meeting, $attendee)) {
                $sent++;
            }
        }

        $meeting->companies()->whereNull('invited_at')->update(['invited_at' => now()]);

        if ($sent === 0) {
            return back()->with('error', 'No invitations could be sent. Add participants first or check your mail settings.');
        }

        return back()->with('success', $sent.' invitation email(s) sent.');
    }

    public function remindInvitations(Meeting $meeting)
    {
        $sent = 0;
        foreach ($meeting->attendees as $attendee) {
            if ($this->sendReminder($meeting, $attendee)) {
                $sent++;
            }
        }

        if ($sent === 0) {
            return back()->with('error', 'No reminders could be sent. Everyone has already responded or has no email.');
        }

        return back()->with('success', $sent.' reminder email(s) sent.');
    }

    public function remindCompany(Meeting $meeting, MeetingCompany $company)
    {
        abort_unless($company->meeting_id === $meeting->id, 404);

        $sent = 0;
        foreach ($company->attendees()->get() as $attendee) {
            if ($this->sendReminder($meeting, $attendee)) {
                $sent++;
            }
        }

        if ($sent === 0) {
            return back()->with('error', 'No reminders could be sent to '.$company->name.'.');
        }

        return back()->with('success', $sent.' reminder(s) sent to '.$company->name.'.');
    }

    public function approveAttendee(Attendee $attendee)
    {
        $meeting = $attendee->meeting;

        abort_if($meeting === null, 404);

        $attendee->forceFill(['requires_approval' => false])->save();
        $sent = $this->sendInvitation($meeting, $attendee);

        return back()->with($sent ? 'success' : 'error', $sent
            ? $attendee->full_name.' approved and invited.'
            : $attendee->full_name.' approved, but the invitation email could not be sent.');
    }

    public function rejectAttendee(Attendee $attendee)
    {
        $name = $attendee->full_name;
        $attendee->delete();

        return back()->with('success', $name.' was not approved and has been removed.');
    }

    public function issueCompanyInvitation(Meeting $meeting, MeetingCompany $company)
    {
        abort_unless($company->meeting_id === $meeting->id, 404);
        abort_if($company->is_host_company, 403, 'The host company does not use an invitation link.');

        $company->issueInvitation($meeting->end_date);

        if (! empty($company->contact_email)) {
            try {
                Mail::to($company->contact_email)->send(new CompanyInvitation($meeting, $company));
            } catch (\Throwable $exception) {
                Log::warning('Failed to email company invitation link to '.$company->contact_email.': '.$exception->getMessage());

                return back()->with('success', 'Invitation link generated for '.$company->name.'. Email could not be sent — copy the link from the panel.');
            }
        }

        return back()->with('success', 'Invitation link generated for '.$company->name.'.');
    }

    public function revokeCompanyInvitation(Meeting $meeting, MeetingCompany $company)
    {
        abort_unless($company->meeting_id === $meeting->id, 404);

        $company->revokeInvitation();

        return back()->with('success', $company->name.' invitation link revoked.');
    }

    public function resendCompanyInvitation(Meeting $meeting, MeetingCompany $company)
    {
        abort_unless($company->meeting_id === $meeting->id, 404);
        abort_if($company->is_host_company, 403, 'The host company does not use an invitation link.');

        $result = $this->issueAndSendCompanyInvitation($meeting, $company);

        if ($result['success']) {
            return back()->with('success', 'Invitation resent to '.$company->contact_email.'.');
        }

        return back()->with('error', 'Failed to resend invitation: '.($result['error'] ?? 'Unknown error'));
    }

    /**
     * Issue invitation token and send email to company contact.
     * Returns array with success status and error message if failed.
     */
    private function issueAndSendCompanyInvitation(Meeting $meeting, MeetingCompany $company): array
    {
        abort_if($company->is_host_company, 403, 'The host company does not use an invitation link.');

        // Issue new invitation token (or reuse existing if not sent)
        if (blank($company->invitation_token) || $company->invitation_status === 'cancelled') {
            $company->issueInvitation($meeting->end_date);
        }

        $company->update([
            'invitation_delivery_status' => 'pending',
            'invitation_failure_reason' => null,
            'invitation_retry_count' => 0,
        ]);

        if (empty($company->contact_email)) {
            $company->update([
                'invitation_delivery_status' => 'failed',
                'invitation_failure_reason' => 'No contact email provided for this company.',
            ]);

            return ['success' => false, 'error' => 'No contact email provided for this company.'];
        }

        try {
            Mail::to($company->contact_email)->send(new CompanyInvitation($meeting, $company));
            $company->update([
                'invitation_delivery_status' => 'sent',
                'invitation_sent_at' => now(),
            ]);

            return ['success' => true];
        } catch (\Throwable $exception) {
            Log::warning('Failed to email company invitation link to '.$company->contact_email.': '.$exception->getMessage());
            $company->update([
                'invitation_delivery_status' => 'failed',
                'invitation_failure_reason' => $exception->getMessage(),
                'invitation_retry_count' => $company->invitation_retry_count + 1,
            ]);

            return ['success' => false, 'error' => $exception->getMessage()];
        }
    }

    public function approveParticipantApproval(ParticipantApproval $approval)
    {
        $approval->load(['meeting', 'attendee']);

        abort_if($approval->meeting === null, 404);

        if ($approval->isMismatch() && $approval->attendee && $approval->requested_company_id) {
            $approval->attendee->forceFill(['meeting_company_id' => $approval->requested_company_id])->save();
        }

        if ($approval->attendee) {
            $approval->attendee->forceFill(['requires_approval' => false])->save();
            $this->sendInvitation($approval->meeting, $approval->attendee);
        }

        $approval->markApproved(auth()->user());

        return back()->with('success', ($approval->display_name ?: 'The guest').' was approved.');
    }

    public function rejectParticipantApproval(ParticipantApproval $approval)
    {
        $approval->markRejected(auth()->user());

        return back()->with('success', ($approval->display_name ?: 'The guest').' request was rejected.');
    }

    public function updateSettings(Request $request, Meeting $meeting)
    {
        $data = $request->validate([
            'password' => ['nullable', 'string', 'max:60'],
            'waiting_room' => ['nullable', 'boolean'],
            'waiting_room_message' => ['nullable', 'string', 'max:280'],
            'mute_on_entry' => ['nullable', 'boolean'],
            'allow_participant_unmute' => ['nullable', 'boolean'],
            'allow_participant_camera' => ['nullable', 'boolean'],
            'default_mic_on' => ['nullable', 'boolean'],
            'default_camera_on' => ['nullable', 'boolean'],
            'allow_screen_share' => ['nullable', 'boolean'],
            'allow_chat' => ['nullable', 'boolean'],
            'allow_participant_hand' => ['nullable', 'boolean'],
            'co_host' => ['nullable', 'string', 'max:160'],
            'co_host_permissions' => ['nullable', 'array'],
            'co_host_permissions.*' => ['string', Rule::in(array_keys(OnlineSessionParticipant::CO_HOST_PERMISSIONS))],
            'allow_recording' => ['nullable', 'boolean'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'company_self_invite' => ['nullable', 'boolean'],
        ]);

        $booleanKeys = [
            'waiting_room', 'mute_on_entry', 'allow_participant_unmute', 'allow_participant_camera',
            'default_mic_on', 'default_camera_on', 'allow_screen_share', 'allow_chat',
            'allow_participant_hand', 'allow_recording', 'company_self_invite',
        ];

        $settings = $meeting->sessionSettings();
        foreach ($booleanKeys as $key) {
            $settings[$key] = $request->boolean($key);
        }
        $settings['password'] = $data['password'] ?? null;
        $settings['waiting_room_message'] = $data['waiting_room_message'] ?? null;
        $settings['co_host'] = $data['co_host'] ?? null;
        $settings['co_host_permissions'] = array_values(array_intersect(
            array_keys(OnlineSessionParticipant::CO_HOST_PERMISSIONS),
            $data['co_host_permissions'] ?? [],
        ));
        $settings['max_participants'] = $data['max_participants'] ?? null;

        $meeting->update(['settings' => $settings]);

        return back()->with('success', 'Meeting settings saved.');
    }

    public function searchCompanies(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $names = MeetingCompany::query()
            ->when($term !== '', fn ($query) => $query->where('name', 'like', '%'.$term.'%'))
            ->orderBy('name')
            ->distinct()
            ->limit(12)
            ->pluck('name')
            ->values();

        return response()->json(['companies' => $names]);
    }

    public function lobby(Meeting $meeting)
    {
        $joinUrl = $meeting->joinUrl();
        $settings = $meeting->sessionSettings();

        return view('meetings.lobby', compact('meeting', 'joinUrl', 'settings'));
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        return $digits === '' ? '0000000000' : substr($digits, 0, 15);
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
        $topicCategoryRules = $meeting->isHybrid()
            ? ['nullable', 'in:Planning,Team meeting,Training,Workshop,Interview,Other,Custom']
            : ['nullable', 'in:Planning,Team meeting,Training,Workshop,Interview,Other'];
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'regex:/^[0-9]{1,10}$/'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'meeting_topic' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'topic_category' => $topicCategoryRules,
            'custom_topic' => [$meeting->isHybrid() ? 'required_if:topic_category,Custom' : 'prohibited', 'nullable', 'string', 'max:80'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'from_location' => ['nullable', 'string', 'max:160'],
            'to_location' => ['nullable', 'string', 'max:160'],
            'physical_location' => ['nullable', 'string', 'max:255'],
            'meeting_room_id' => [$meeting->isHybrid() ? 'required' : 'nullable', 'exists:meeting_rooms,id'],
            'notes' => ['nullable', 'string'],
            // Hybrid meeting specific fields
            'venue_name' => ['nullable', 'string', 'max:255'],
            'venue_address' => ['nullable', 'string'],
            'venue_building_room' => ['nullable', 'string', 'max:255'],
            'venue_instructions' => ['nullable', 'string'],
            'venue_capacity' => ['nullable', 'integer', 'min:1'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'require_registration' => ['nullable', 'boolean'],
            'allow_rsvp_maybe' => ['nullable', 'boolean'],
            'allow_rsvp_another_time' => ['nullable', 'boolean'],
            'enable_document_sharing' => ['nullable', 'boolean'],
            'allow_participant_upload' => ['nullable', 'boolean'],
            // Online meeting settings
            'waiting_room' => ['nullable', 'boolean'],
            'waiting_room_message' => ['nullable', 'string', 'max:500'],
            'mute_on_entry' => ['nullable', 'boolean'],
            'allow_participant_unmute' => ['nullable', 'boolean'],
            'allow_participant_camera' => ['nullable', 'boolean'],
            'allow_screen_share' => ['nullable', 'boolean'],
            'allow_chat' => ['nullable', 'boolean'],
            'allow_recording' => ['nullable', 'boolean'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'reminder_before_minutes' => ['nullable', 'integer', 'min:5'],
            'reminder_before_hours' => ['nullable', 'integer', 'min:1'],
            'reminder_before_days' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($meeting->isHybrid() && ($data['topic_category'] ?? null) === 'Custom') {
            $data['topic_category'] = $data['custom_topic'];
        }
        unset($data['custom_topic']);

        // Extract hybrid meeting specific fields
        $hybridFields = [
            'venue_name' => $data['venue_name'] ?? null,
            'venue_address' => $data['venue_address'] ?? null,
            'venue_building_room' => $data['venue_building_room'] ?? null,
            'venue_instructions' => $data['venue_instructions'] ?? null,
            'venue_capacity' => $data['venue_capacity'] ?? null,
            'timezone' => $data['timezone'] ?? $meeting->timezone,
            'require_registration' => $request->boolean('require_registration'),
            'allow_rsvp_maybe' => $request->boolean('allow_rsvp_maybe'),
            'allow_rsvp_another_time' => $request->boolean('allow_rsvp_another_time'),
            'enable_document_sharing' => $request->boolean('enable_document_sharing'),
            'allow_participant_upload' => $request->boolean('allow_participant_upload'),
        ];
        foreach (array_keys($hybridFields) as $key) {
            unset($data[$key]);
        }

        // Extract online meeting settings for settings JSON
        $onlineSettings = [];
        $settingsFields = [
            'waiting_room', 'waiting_room_message', 'mute_on_entry',
            'allow_participant_unmute', 'allow_participant_camera',
            'allow_screen_share', 'allow_chat', 'allow_recording',
            'max_participants', 'reminder_before_minutes',
            'reminder_before_hours', 'reminder_before_days',
        ];
        foreach ($settingsFields as $field) {
            if (isset($data[$field])) {
                $onlineSettings[$field] = $data[$field];
                unset($data[$field]);
            }
        }

        if ($this->scheduleConflicts($data['start_date'], $data['end_date'], $meeting->id, $data['meeting_room_id'] ?? null)) {
            return back()->withInput()->withErrors([
                'start_date' => 'This time overlaps another booked meeting. Choose a free date and time.',
            ]);
        }

        $originalStart = $meeting->start_date?->toDateTimeString();
        $originalEnd = $meeting->end_date?->toDateTimeString();

        $meeting->update(array_merge($data, $hybridFields, [
            'cancelled_at' => null,
            'status' => $meeting->isCancelled() ? 'scheduled' : $meeting->status,
        ]));

        // Update settings with online meeting settings
        if (! empty($onlineSettings)) {
            $currentSettings = $meeting->settings ?? [];
            $meeting->update(['settings' => array_merge($currentSettings, $onlineSettings)]);
        }

        $scheduleChanged = $originalStart !== $meeting->start_date?->toDateTimeString()
            || $originalEnd !== $meeting->end_date?->toDateTimeString();

        if ($scheduleChanged) {
            app(InvitationNotificationService::class)->meetingRescheduled($meeting->fresh());
        }

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

        $endedAt = now();
        $meeting->update([
            'started_at' => $meeting->started_at ?? now(),
            'ended_at' => $endedAt,
            'status' => 'completed',
        ]);

        // Apply document retention policies
        if ($meeting->is_online) {
            $this->applyDocumentRetentionPolicies($meeting);

            $participants = OnlineSessionParticipant::with('attendee')
                ->where('meeting_id', $meeting->id)
                ->whereNull('left_at')
                ->get();
            foreach ($participants as $participant) {
                // Invalidate the participant's token by clearing it
                $participant->update([
                    'left_at' => $endedAt,
                    'hand_raised' => false,
                    'token_hash' => null, // Invalidate token to prevent rejoining
                ]);
                $participant->attendee?->update(['checked_out_at' => $endedAt]);
            }
        }

        return back()->with('success', 'Meeting ended. All participants have been disconnected.');
    }

    public function cancel(Meeting $meeting)
    {
        if ($meeting->isCancelled()) {
            return back()->with('error', 'This meeting is already cancelled.');
        }

        if ($meeting->status === 'completed') {
            return back()->with('error', 'A completed meeting cannot be cancelled.');
        }

        $cancelledAt = now();
        $meeting->update([
            'status' => 'cancelled',
            'cancelled_at' => $cancelledAt,
            'started_at' => null,
            'ended_at' => null,
        ]);

        // Apply document retention policies on cancellation
        if ($meeting->is_online) {
            $this->applyDocumentRetentionPolicies($meeting);

            $participants = OnlineSessionParticipant::with('attendee')
                ->where('meeting_id', $meeting->id)
                ->whereNull('left_at')
                ->get();
            foreach ($participants as $participant) {
                $participant->update(['left_at' => $cancelledAt, 'hand_raised' => false]);
                $participant->attendee?->update(['checked_out_at' => $cancelledAt]);
            }
        }

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
        $meeting->load(['room', 'attendees']);

        return Pdf::loadView('meetings.pdf', compact('meeting'))
            ->stream('meeting-'.$meeting->meeting_code.'.pdf');
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

        $notified = app(InvitationNotificationService::class)->meetingRescheduled($meeting);

        $message = 'Meeting rescheduled successfully.';
        if ($notified > 0) {
            $message .= ' '.$notified.' participant(s) notified of the new time.';
        }

        return back()->with('success', $message);
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

        $attendee->update([
            'checked_in_at' => $attendee->checked_in_at ?? now(),
            'checked_out_at' => null,
            'attendance_mode' => $meeting->isHybrid() ? 'in_person' : $attendee->attendance_mode,
        ]);

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

        $attendee->update([
            'checked_in_at' => $attendee->checked_in_at ?? now(),
            'checked_out_at' => null,
            'attendance_mode' => $meeting->isHybrid() ? 'in_person' : $attendee->attendance_mode,
        ]);

        return redirect()
            ->route('kiosk.show', $meeting->meeting_code)
            ->with('kiosk_success', $attendee->full_name);
    }

    public function undoCheckIn(Attendee $attendee)
    {
        $attendee->update(['checked_in_at' => null, 'checked_out_at' => null, 'attendance_mode' => null]);

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
            'phone' => ['required', 'regex:/^[0-9]{1,10}$/'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'department' => ['nullable', 'string', 'max:160'],
        ]);
        if ($meeting->isMultiCompany() && ! empty($data['company_name'])) {
            $data['meeting_company_id'] = MeetingCompany::forName($meeting, $data['company_name'])->id;
        }
        $data['checked_in_at'] = $request->has('mark_present')
            ? ($request->boolean('mark_present') ? now() : null)
            : now();
        $data['attendance_mode'] = $data['checked_in_at'] && $meeting->isHybrid() ? 'in_person' : null;
        $attendee = $meeting->attendees()->create($data);

        $sent = $this->sendInvitation($meeting, $attendee);

        $message = 'Attendee added.'.($attendee->checked_in_at ? ' Marked present.' : '');
        $message .= $sent
            ? ' Attendee code emailed to '.$attendee->email.'.'
            : ' The invitation email could not be sent. Please check mail settings and retry.';

        return back()->with($sent ? 'success' : 'error', $message);
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
        \Log::info('sendInvitation called', ['meeting_id' => $meeting->id, 'attendee_id' => $attendee->id, 'attendee_email' => $attendee->email]);

        try {
            $attendee->ensureInvitationToken();

            if (! $attendee->invitation_expires_at) {
                $attendee->forceFill(['invitation_expires_at' => $meeting->end_date])->save();
            }

            \Log::info('Sending mail via Mail facade', ['attendee_email' => $attendee->email]);

            Mail::to($attendee->email)->send(new AttendeeInvitation($meeting, $attendee));

            \Log::info('Mail facade send completed');

            $attendee->markSent();
            app(InvitationNotificationService::class)->recipientInvited($meeting, $attendee);

            return true;
        } catch (\Throwable $exception) {
            \Log::error('Failed to send attendee invitation', [
                'attendee_email' => $attendee->email,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return false;
        }
    }

    private function sendReminder(Meeting $meeting, Attendee $attendee): bool
    {
        if (in_array($attendee->invitationStatus(), ['accepted', 'declined'], true)) {
            return false;
        }

        try {
            $attendee->ensureInvitationToken();

            Mail::to($attendee->email)->send(new InvitationReminder($meeting, $attendee));

            $attendee->forceFill(['reminder_sent_at' => now()])->save();
            if ($attendee->invitation_sent_at === null) {
                $attendee->markSent();
            }

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Failed to send reminder to '.$attendee->email.': '.$exception->getMessage());

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

    private function createMeetingSeries(Meeting $meeting, array $recurrenceData, $user): MeetingSeries
    {
        $endDate = null;
        $maxOccurrences = null;

        if ($recurrenceData['recurrence_end_type'] === 'on_date' && $recurrenceData['recurrence_end_date']) {
            $endDate = Carbon::parse($recurrenceData['recurrence_end_date'])->endOfDay();
        } elseif ($recurrenceData['recurrence_end_type'] === 'after_occurrences' && $recurrenceData['recurrence_max_occurrences']) {
            $maxOccurrences = (int) $recurrenceData['recurrence_max_occurrences'];
        }

        $series = MeetingSeries::create([
            'name' => $meeting->name,
            'meeting_topic' => $meeting->meeting_topic,
            'description' => $meeting->description,
            'frequency' => $recurrenceData['frequency'],
            'interval' => $recurrenceData['interval'],
            'days_of_week' => $recurrenceData['days_of_week'] ?? null,
            'day_of_month' => $recurrenceData['day_of_month'] ?? null,
            'start_date' => $meeting->start_date,
            'end_date' => $meeting->end_date,
            'recurrence_end_date' => $endDate,
            'max_occurrences' => $maxOccurrences,
            'settings' => $meeting->settings,
            'is_active' => true,
            'host_user_id' => $user?->id,
            'timezone' => config('app.timezone', 'UTC'),
        ]);

        // Create first occurrence record
        $series->occurrences()->create([
            'occurrence_number' => 1,
            'meeting_id' => $meeting->id,
            'scheduled_start' => $meeting->start_date,
            'scheduled_end' => $meeting->end_date,
            'status' => 'generated',
        ]);

        $series->increment('occurrences_count');

        // Generate upcoming occurrences (next 10)
        $series->generateOccurrences(10);

        return $series;
    }

    /**
     * Show the edit options for a recurring meeting occurrence.
     */
    public function editSeriesOccurrence(Meeting $meeting)
    {
        abort_unless($meeting->series_id, 404, 'This meeting is not part of a recurring series.');

        $meeting->load(['series', 'series.occurrences']);
        $occurrence = $meeting->series->occurrences()
            ->where('occurrence_number', $meeting->occurrence_number)
            ->first();

        return view('meetings.edit-series-occurrence', compact('meeting', 'occurrence'));
    }

    /**
     * Update a recurring meeting occurrence with options: this only, all future, all occurrences.
     */
    public function updateSeriesOccurrence(Request $request, Meeting $meeting)
    {
        abort_unless($meeting->series_id, 404, 'This meeting is not part of a recurring series.');

        $scope = $request->validate([
            'scope' => ['required', 'in:this_only,all_future,all_occurrences'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after:start_date'],
            'meeting_room_id' => ['sometimes', 'nullable', 'exists:meeting_rooms,id'],
            'physical_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'in:scheduled,cancelled'],
        ])['scope'];

        $series = $meeting->series;
        $occurrenceNumber = $meeting->occurrence_number;

        switch ($scope) {
            case 'this_only':
                return $this->updateSingleOccurrence($request, $meeting, $series, $occurrenceNumber);
            case 'all_future':
                return $this->updateFutureOccurrences($request, $meeting, $series, $occurrenceNumber);
            case 'all_occurrences':
                return $this->updateAllOccurrences($request, $meeting, $series);
        }

        return back()->with('error', 'Invalid scope.');
    }

    /**
     * Update only this occurrence - creates an exception.
     */
    private function updateSingleOccurrence(Request $request, Meeting $meeting, MeetingSeries $series, int $occurrenceNumber)
    {
        $data = $request->validate([
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after:start_date'],
            'meeting_room_id' => ['sometimes', 'nullable', 'exists:meeting_rooms,id'],
            'physical_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'in:scheduled,cancelled'],
        ]);

        $originalStart = $meeting->start_date?->toDateTimeString();
        $originalEnd = $meeting->end_date?->toDateTimeString();

        $meeting->update($data);

        $scheduleChanged = $originalStart !== $meeting->start_date?->toDateTimeString()
            || $originalEnd !== $meeting->end_date?->toDateTimeString();

        // Create or update exception
        $exception = $series->exceptions()->updateOrCreate(
            ['occurrence_number' => $occurrenceNumber],
            [
                'exception_type' => $request->input('status') === 'cancelled' ? 'cancelled' : ($scheduleChanged ? 'rescheduled' : 'modified'),
                'original_start' => $originalStart,
                'original_end' => $originalEnd,
                'new_start' => $scheduleChanged ? $meeting->start_date : null,
                'new_end' => $scheduleChanged ? $meeting->end_date : null,
                'modified_fields' => array_keys($data),
                'meeting_id' => $meeting->id,
                'requires_approval' => $scheduleChanged && $meeting->attendees()->where('invitation_status', 'accepted')->exists(),
            ]
        );

        // Update occurrence status
        $occurrence = $series->occurrences()
            ->where('occurrence_number', $occurrenceNumber)
            ->first();
        if ($occurrence) {
            $occurrence->update([
                'status' => $request->input('status') === 'cancelled' ? 'cancelled' : ($scheduleChanged ? 'modified' : 'generated'),
                'scheduled_start' => $meeting->start_date,
                'scheduled_end' => $meeting->end_date,
            ]);
        }

        // Notify if schedule changed and requires approval
        if ($scheduleChanged && $exception->requires_approval) {
            app(InvitationNotificationService::class)->meetingRescheduled($meeting);

            return back()->with('success', 'Occurrence updated. Organizer approval required for accepted attendees.');
        }

        if ($scheduleChanged) {
            app(InvitationNotificationService::class)->meetingRescheduled($meeting);
        }

        return back()->with('success', 'This occurrence updated successfully.');
    }

    /**
     * Update this and all future occurrences.
     */
    private function updateFutureOccurrences(Request $request, Meeting $meeting, MeetingSeries $series, int $occurrenceNumber)
    {
        $data = $request->validate([
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after:start_date'],
            'meeting_room_id' => ['sometimes', 'nullable', 'exists:meeting_rooms,id'],
            'physical_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'in:scheduled,cancelled'],
        ]);

        $originalStart = $meeting->start_date?->toDateTimeString();
        $originalEnd = $meeting->end_date?->toDateTimeString();

        // Update current occurrence
        $meeting->update($data);

        $scheduleChanged = $originalStart !== $meeting->start_date?->toDateTimeString()
            || $originalEnd !== $meeting->end_date?->toDateTimeString();

        // Create exception for current occurrence
        $exception = $series->exceptions()->updateOrCreate(
            ['occurrence_number' => $occurrenceNumber],
            [
                'exception_type' => $request->input('status') === 'cancelled' ? 'cancelled' : ($scheduleChanged ? 'rescheduled' : 'modified'),
                'original_start' => $originalStart,
                'original_end' => $originalEnd,
                'new_start' => $scheduleChanged ? $meeting->start_date : null,
                'new_end' => $scheduleChanged ? $meeting->end_date : null,
                'modified_fields' => array_keys($data),
                'meeting_id' => $meeting->id,
                'requires_approval' => $scheduleChanged && $meeting->attendees()->where('invitation_status', 'accepted')->exists(),
            ]
        );

        // Update all future occurrences (create exceptions for each)
        $futureOccurrences = $series->occurrences()
            ->where('occurrence_number', '>', $occurrenceNumber)
            ->where('status', '!=', 'cancelled')
            ->get();

        foreach ($futureOccurrences as $futureOccurrence) {
            $futureMeeting = $futureOccurrence->meeting;
            if ($futureMeeting) {
                $futureMeeting->update($data);
            }

            $series->exceptions()->updateOrCreate(
                ['occurrence_number' => $futureOccurrence->occurrence_number],
                [
                    'exception_type' => $request->input('status') === 'cancelled' ? 'cancelled' : ($scheduleChanged ? 'rescheduled' : 'modified'),
                    'original_start' => $futureOccurrence->scheduled_start,
                    'original_end' => $futureOccurrence->scheduled_end,
                    'new_start' => $scheduleChanged ? $meeting->start_date : null,
                    'new_end' => $scheduleChanged ? $meeting->end_date : null,
                    'modified_fields' => array_keys($data),
                    'meeting_id' => $futureMeeting?->id,
                    'requires_approval' => $scheduleChanged && ($futureMeeting?->attendees()->where('invitation_status', 'accepted')->exists() ?? false),
                ]
            );

            $futureOccurrence->update([
                'status' => $request->input('status') === 'cancelled' ? 'cancelled' : ($scheduleChanged ? 'modified' : 'generated'),
                'scheduled_start' => $meeting->start_date,
                'scheduled_end' => $meeting->end_date,
            ]);
        }

        // Also update the series template for future generated occurrences
        if ($scheduleChanged) {
            $series->update([
                'start_date' => $meeting->start_date,
                'end_date' => $meeting->end_date,
            ]);
        }

        if ($scheduleChanged) {
            app(InvitationNotificationService::class)->meetingRescheduled($meeting);
        }

        return back()->with('success', 'This and all future occurrences updated successfully.');
    }

    /**
     * Update all occurrences in the series (including past ones).
     */
    private function updateAllOccurrences(Request $request, Meeting $meeting, MeetingSeries $series)
    {
        $data = $request->validate([
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after:start_date'],
            'meeting_room_id' => ['sometimes', 'nullable', 'exists:meeting_rooms,id'],
            'physical_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'in:scheduled,cancelled'],
        ]);

        // Update series template
        if (isset($data['start_date']) || isset($data['end_date'])) {
            $series->update([
                'start_date' => $data['start_date'] ?? $series->start_date,
                'end_date' => $data['end_date'] ?? $series->end_date,
            ]);
        }

        // Update all meetings in the series
        $allMeetings = Meeting::where('series_id', $series->id)->get();

        foreach ($allMeetings as $seriesMeeting) {
            $seriesMeeting->update($data);
        }

        // Update all occurrence records
        $series->occurrences()->update([
            'status' => $request->input('status') === 'cancelled' ? 'cancelled' : 'modified',
        ]);

        // Create exceptions for all occurrences
        $occurrences = $series->occurrences()->get();
        foreach ($occurrences as $occurrence) {
            $series->exceptions()->updateOrCreate(
                ['occurrence_number' => $occurrence->occurrence_number],
                [
                    'exception_type' => $request->input('status') === 'cancelled' ? 'cancelled' : 'modified',
                    'original_start' => $occurrence->scheduled_start,
                    'original_end' => $occurrence->scheduled_end,
                    'new_start' => $data['start_date'] ?? $occurrence->scheduled_start,
                    'new_end' => $data['end_date'] ?? $occurrence->scheduled_end,
                    'modified_fields' => array_keys($data),
                    'meeting_id' => $occurrence->meeting_id,
                    'requires_approval' => ($data['start_date'] || $data['end_date']) && ($occurrence->meeting?->attendees()->where('invitation_status', 'accepted')->exists() ?? false),
                ]
            );
        }

        if (isset($data['start_date']) || isset($data['end_date'])) {
            app(InvitationNotificationService::class)->meetingRescheduled($meeting);
        }

        return back()->with('success', 'All occurrences in the series updated successfully.');
    }

    /**
     * Approve a meeting exception that required organizer approval.
     */
    public function approveMeetingException(MeetingException $exception)
    {
        $exception->approve(auth()->user());

        return back()->with('success', 'Exception approved and applied.');
    }

    /**
     * Reject a meeting exception that required organizer approval.
     */
    public function rejectMeetingException(MeetingException $exception)
    {
        $exception->reject(auth()->user());

        return back()->with('success', 'Exception rejected. Changes reverted.');
    }

    /**
     * Apply document retention policies when a meeting ends.
     */
    private function applyDocumentRetentionPolicies(Meeting $meeting): void
    {
        $documents = DocumentShare::where('meeting_id', $meeting->id)
            ->where('status', DocumentShare::STATUS_ACTIVE)
            ->get();

        foreach ($documents as $document) {
            $document->applyRetentionPolicy();

            // Log the retention policy application
            DocumentAccessLog::create([
                'document_share_id' => $document->id,
                'participant_id' => null, // System action
                'action' => DocumentAccessLog::ACTION_REVOKE,
                'ip_address' => null,
                'user_agent' => 'System',
                'metadata' => [
                    'retention_policy' => $document->retention_policy,
                    'trigger' => 'meeting_ended',
                ],
            ]);
        }
    }

    /**
     * Lock the meeting to prevent new participants from joining.
     */
    public function lock(Meeting $meeting)
    {
        abort_unless(auth()->id() === $meeting->host_user_id || auth()->user()->isAdmin(), 403);

        if ($meeting->isLocked()) {
            return back()->with('error', 'Meeting is already locked.');
        }

        $meeting->lock(auth()->user());

        AuditLogService::meetingLocked($meeting, auth()->user());

        return back()->with('success', 'Meeting locked. No new participants can join.');
    }

    /**
     * Unlock the meeting to allow participants to join.
     */
    public function unlock(Meeting $meeting)
    {
        abort_unless(auth()->id() === $meeting->host_user_id || auth()->user()->isAdmin(), 403);

        if (! $meeting->isLocked()) {
            return back()->with('error', 'Meeting is not locked.');
        }

        $meeting->unlock();

        AuditLogService::meetingUnlocked($meeting, auth()->user());

        return back()->with('success', 'Meeting unlocked. Participants can now join.');
    }

    public function downloadTemplate()
    {
        $filename = 'attendees-template.csv';
        $content = "Full Name,Email,Phone,Company,Department\n";

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
