<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Company;
use App\Models\Meeting;
use App\Models\MeetingRoom;
use App\Models\OnlineSessionParticipant;
use App\Models\OnlineSessionRecording;
use App\Models\OnlineSessionSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminToolsController extends Controller
{
    public function users()
    {
        $users = User::orderBy('id')->paginate(20);

        return view('admin.users', compact('users'));
    }

    public function reports()
    {
        $months = collect(range(5, 0))->map(function (int $monthsAgo) {
            $start = now()->subMonths($monthsAgo)->startOfMonth();
            $end = now()->subMonths($monthsAgo)->endOfMonth();
            $total = Meeting::whereBetween('start_date', [$start, $end])->count();
            $completed = Meeting::whereBetween('start_date', [$start, $end])->where('status', 'completed')->count();
            $online = Meeting::whereBetween('start_date', [$start, $end])->where('is_online', true)->count();
            $hybrid = Meeting::whereBetween('start_date', [$start, $end])->where('meeting_type', 'hybrid')->count();

            return [
                'label' => $start->format('M Y'),
                'total' => $total,
                'completed' => $completed,
                'online' => $online,
                'hybrid' => $hybrid,
            ];
        })->reverse();

        $meetingCount = Meeting::count();
        $attendeeCount = Attendee::count();
        $checkedInCount = Attendee::whereNotNull('checked_in_at')->count();
        $attendanceRate = $attendeeCount ? round($checkedInCount / $attendeeCount * 100) : 0;
        $statusCounts = Meeting::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $monthlyMeetings = $months;
        $rooms = MeetingRoom::withCount(['meetings' => fn ($q) => $q->where('status', '!=', 'cancelled')])->get();

        return view('admin.reports', compact('months', 'meetingCount', 'attendeeCount', 'checkedInCount', 'attendanceRate', 'statusCounts', 'monthlyMeetings', 'rooms'));
    }

    public function expenses()
    {
        return view('admin.expenses');
    }

    public function notifications()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(20);

        // Build inbox from notification data
        $inbox = collect($notifications->items())
            ->filter(function ($notification) {
                return in_array($notification->type, [
                    'App\Notifications\InvitationResponseReceived',
                    'App\Notifications\MeetingInvitationReceived',
                ]);
            })
            ->map(function ($notification) {
                $data = $notification->data;

                return [
                    'title' => $data['title'] ?? 'Notification',
                    'message' => $data['message'] ?? '',
                    'date' => $notification->created_at,
                    'kind' => $data['kind'] ?? 'invitation_response',
                    'response' => $data['response'] ?? null,
                    'url' => $data['url'] ?? null,
                ];
            })
            ->values();

        // Get live meetings for schedule alerts
        $liveMeetings = Meeting::where('status', 'in_progress')
            ->whereNotNull('started_at')
            ->orderBy('started_at')
            ->get();

        return view('admin.notifications', compact('notifications', 'inbox', 'liveMeetings'));
    }

    public function settings()
    {
        $recordingSettings = OnlineSessionSetting::current();
        $recordingUsers = User::where('can_record_sessions', true)->orderBy('name')->get();
        $allUsers = User::orderBy('name')->get();
        $recordings = OnlineSessionRecording::with('meeting:id,meeting_topic,meeting_code', 'participant:id,display_name')
            ->latest('started_at')
            ->get();
        $recordingCount = $recordings->where('status', 'ready')->count();
        $storageBytes = $recordings->sum('file_size');

        return view('admin.settings', compact('recordingSettings', 'recordingUsers', 'allUsers', 'recordings', 'recordingCount', 'storageBytes'));
    }

    public function updateRecordingSettings(Request $request)
    {
        $data = $request->validate([
            'recording_enabled' => ['required', 'boolean'],
            'allow_attendee_recording' => ['required', 'boolean'],
            'downloads_for_hosts' => ['required', 'boolean'],
        ]);

        $settings = OnlineSessionSetting::current();
        $settings->update($data);

        return redirect()->route('settings.index')->with('success', 'Recording and storage settings saved.');
    }

    public function updateRecordingPermission(Request $request, User $user)
    {
        $data = $request->validate([
            'can_record_sessions' => ['boolean'],
        ]);
        $user->update($data);

        return redirect()->route('settings.index')->with('success', 'Recording permission updated for '.$user->name.'.');
    }

    /**
     * Admin Live Session Monitor - real-time view of all active multi-company sessions
     */
    public function liveSessionMonitor(): View
    {
        $now = now();

        // Get all live multi-company meetings
        $liveMeetings = Meeting::with([
            'companies' => fn ($q) => $q->with(['attendees' => fn ($a) => $a->orderBy('full_name')])
                ->orderByDesc('is_host_company')
                ->orderBy('name'),
            'room',
        ])
            ->where('is_online', true)
            ->where('is_multi_company', true)
            ->where('status', 'in_progress')
            ->orderBy('start_date')
            ->get();

        // Get all live meetings (including single-company) for broader monitoring
        $allLiveMeetings = Meeting::with(['companies', 'room'])
            ->where('is_online', true)
            ->where('status', 'in_progress')
            ->orderBy('start_date')
            ->get();

        $monitorData = $allLiveMeetings->map(function (Meeting $meeting) {
            $activeParticipants = OnlineSessionParticipant::with(['attendee', 'company'])
                ->where('meeting_id', $meeting->id)
                ->whereNull('left_at')
                ->where('status', OnlineSessionParticipant::STATUS_ADMITTED)
                ->get();

            $waitingParticipants = OnlineSessionParticipant::with(['attendee', 'company'])
                ->where('meeting_id', $meeting->id)
                ->whereNull('left_at')
                ->where('status', OnlineSessionParticipant::STATUS_WAITING)
                ->get();

            $activeRecordings = OnlineSessionRecording::with('participant:id,display_name')
                ->where('meeting_id', $meeting->id)
                ->where('status', 'recording')
                ->orderBy('started_at')
                ->get();

            // Group participants by company
            $companyStats = $meeting->companies->map(function ($company) use ($activeParticipants, $waitingParticipants) {
                $activeInCompany = $activeParticipants->filter(fn ($p) => $p->company_id === $company->id);
                $waitingInCompany = $waitingParticipants->filter(fn ($p) => $p->company_id === $company->id);
                $invitedAttendees = $company->attendees->count();

                return [
                    'company' => $company,
                    'active_count' => $activeInCompany->count(),
                    'waiting_count' => $waitingInCompany->count(),
                    'invited_count' => $invitedAttendees,
                    'active_participants' => $activeInCompany,
                    'waiting_participants' => $waitingInCompany,
                ];
            });

            // Host company (may not be in companies relation)
            $hostCompany = $meeting->companies->firstWhere('is_host_company', true);
            $hostCompanyActive = $activeParticipants->filter(fn ($p) => $p->is_host);
            $hostCompanyWaiting = $waitingParticipants->filter(fn ($p) => $p->is_host);

            if ($hostCompany && ($hostCompanyActive->count() > 0 || $hostCompanyWaiting->count() > 0)) {
                $companyStats->prepend([
                    'company' => $hostCompany,
                    'active_count' => $hostCompanyActive->count(),
                    'waiting_count' => $hostCompanyWaiting->count(),
                    'invited_count' => $hostCompany->attendees->count(),
                    'active_participants' => $hostCompanyActive,
                    'waiting_participants' => $hostCompanyWaiting,
                ]);
            }

            return [
                'meeting' => $meeting,
                'is_multi_company' => $meeting->is_multi_company,
                'active_participants' => $activeParticipants,
                'waiting_participants' => $waitingParticipants,
                'active_recordings' => $activeRecordings,
                'company_stats' => $companyStats,
                'total_active' => $activeParticipants->count(),
                'total_waiting' => $waitingParticipants->count(),
            ];
        });

        return view('admin.live-session-monitor', compact('monitorData'));
    }

    /**
     * API endpoint for real-time polling
     */
    public function liveSessionMonitorApi(Request $request)
    {
        $now = now();

        $liveMeetings = Meeting::with(['companies' => fn ($q) => $q->with(['attendees'])])
            ->where('is_online', true)
            ->where('status', 'in_progress')
            ->orderBy('start_date')
            ->get();

        $data = $liveMeetings->map(function (Meeting $meeting) {
            $activeParticipants = OnlineSessionParticipant::with(['attendee', 'company'])
                ->where('meeting_id', $meeting->id)
                ->whereNull('left_at')
                ->where('status', OnlineSessionParticipant::STATUS_ADMITTED)
                ->get();

            $waitingParticipants = OnlineSessionParticipant::with(['attendee', 'company'])
                ->where('meeting_id', $meeting->id)
                ->whereNull('left_at')
                ->where('status', OnlineSessionParticipant::STATUS_WAITING)
                ->get();

            $activeRecordings = OnlineSessionRecording::with('participant:id,display_name')
                ->where('meeting_id', $meeting->id)
                ->where('status', 'recording')
                ->get();

            $companyStats = $meeting->companies->map(function ($company) use ($activeParticipants, $waitingParticipants) {
                $activeInCompany = $activeParticipants->filter(fn ($p) => $p->company_id === $company->id);
                $waitingInCompany = $waitingParticipants->filter(fn ($p) => $p->company_id === $company->id);

                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'logo_path' => $company->logo_path,
                    'is_host_company' => $company->is_host_company,
                    'active_count' => $activeInCompany->count(),
                    'waiting_count' => $waitingInCompany->count(),
                    'invited_count' => $company->attendees->count(),
                    'active_participants' => $activeInCompany->map(fn ($p) => [
                        'id' => $p->id,
                        'peer_id' => $p->peer_id,
                        'name' => $p->display_name,
                        'is_host' => $p->is_host,
                        'is_co_host' => $p->is_co_host,
                        'is_muted' => $p->is_muted_by_host,
                        'hand_raised' => $p->hand_raised,
                    ])->values(),
                    'waiting_participants' => $waitingInCompany->map(fn ($p) => [
                        'id' => $p->id,
                        'peer_id' => $p->peer_id,
                        'name' => $p->display_name,
                        'requested_at' => $p->requested_at?->toIso8601String(),
                    ])->values(),
                ];
            })->values();

            return [
                'meeting_id' => $meeting->id,
                'meeting_code' => $meeting->meeting_code,
                'meeting_topic' => $meeting->meeting_topic,
                'meeting_type' => $meeting->meeting_type,
                'is_multi_company' => $meeting->is_multi_company,
                'started_at' => $meeting->started_at?->toIso8601String(),
                'room_name' => $meeting->room?->name,
                'total_active' => $activeParticipants->count(),
                'total_waiting' => $waitingParticipants->count(),
                'company_stats' => $companyStats,
                'recordings' => $activeRecordings->map(fn ($r) => [
                    'id' => $r->id,
                    'participant_name' => $r->participant?->display_name ?? 'Unknown',
                    'started_at' => $r->started_at?->toIso8601String(),
                ])->values(),
            ];
        })->values();

        return response()->json([
            'timestamp' => $now->toIso8601String(),
            'meetings' => $data,
        ]);
    }

    /**
     * Company Management - List all platform companies
     */
    public function companies(): View
    {
        $query = Company::query();

        // Search
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (request('status') && in_array(request('status'), Company::STATUSES)) {
            $query->where('status', request('status'));
        }

        // Type filter
        if (request('type') && array_key_exists(request('type'), Company::COMPANY_TYPES)) {
            $query->where('company_type', request('type'));
        }

        // Sorting
        $sort = request('sort', 'name');
        switch ($sort) {
            case 'created_desc':
                $query->latest();
                break;
            case 'created_asc':
                $query->oldest();
                break;
            case 'members_desc':
                $query->withCount('memberships')->orderByDesc('memberships_count');
                break;
            default:
                $query->orderBy('name');
        }

        $companies = $query->withCount('memberships')->paginate(20)->withQueryString();

        return view('admin.companies.index', compact('companies'));
    }

    public function createCompany(): View
    {
        return view('admin.companies.create', [
            'companyTypes' => Company::COMPANY_TYPES,
            'statuses' => Company::STATUSES,
        ]);
    }

    public function storeCompany(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:companies,slug'],
            'email' => ['nullable', 'email', 'max:160', 'unique:companies,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'url', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:80'],
            'company_type' => ['nullable', Rule::in(array_keys(Company::COMPANY_TYPES))],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(Company::STATUSES)],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('company-logos', 'public');
        }

        if (empty($data['slug'])) {
            $data['slug'] = Company::generateUniqueSlug($data['name']);
        }

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        $data['status'] = $data['status'] ?? 'active';

        $company = Company::create($data);

        return redirect()->route('admin.companies.show', $company)
            ->with('success', "Company '{$company->name}' created successfully.");
    }

    public function showCompany(Company $company): View
    {
        $company->load(['memberships.user', 'meetingCompanies.meeting']);
        $memberships = $company->memberships()->with('user')->latest()->get();
        $meetings = $company->meetingCompanies()->with('meeting')->latest()->get();

        return view('admin.companies.show', compact('company', 'memberships', 'meetings'));
    }

    public function editCompany(Company $company): View
    {
        return view('admin.companies.edit', compact('company', 'companyTypes', 'statuses'));
    }

    public function updateCompany(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:companies,slug,'.$company->id],
            'email' => ['nullable', 'email', 'max:160', 'unique:companies,email,'.$company->id],
            'phone' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'url', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:80'],
            'company_type' => ['nullable', Rule::in(array_keys(Company::COMPANY_TYPES))],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(Company::STATUSES)],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('company-logos', 'public');
        }

        if (empty($data['slug'])) {
            $data['slug'] = Company::generateUniqueSlug($data['name']);
        }

        $data['updated_by'] = auth()->id();
        $company->update($data);

        return redirect()->route('admin.companies.show', $company)
            ->with('success', "Company '{$company->name}' updated successfully.");
    }

    public function destroyCompany(Company $company): RedirectResponse
    {
        // Check if company has active meetings
        if ($company->meetingCompanies()->whereHas('meeting', fn ($q) => $q->where('status', 'in_progress'))->exists()) {
            return back()->with('error', 'Cannot delete company with active meetings.');
        }

        $name = $company->name;

        if ($company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
        }

        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', "Company '{$name}' deleted successfully.");
    }
}
