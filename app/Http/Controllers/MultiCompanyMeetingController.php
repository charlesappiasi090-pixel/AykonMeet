<?php

namespace App\Http\Controllers;

use App\Mail\CompanyInvitation;
use App\Models\Attendee;
use App\Models\CompanyContact;
use App\Models\Meeting;
use App\Models\MeetingCompany;
use App\Models\MeetingRoom;
use App\Models\OnlineSessionParticipant;
use App\Services\CompanyImporter;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MultiCompanyMeetingController extends Controller
{
    private const SESSION_KEY = 'multi_company_meeting_wizard';

    public function create(Request $request): View
    {
        $step = max(1, min(3, (int) $request->query('step', 1)));
        $rooms = MeetingRoom::orderBy('name')->get();
        $sessionData = $request->session()->get(self::SESSION_KEY, []);

        $steps = [
            [
                'id' => 1,
                'title' => 'Meeting Form',
                'description' => 'Meeting details, schedule, and permissions',
                'icon' => '📋',
            ],
            [
                'id' => 2,
                'title' => 'Add Invited Companies',
                'description' => 'Register companies and primary contact persons',
                'icon' => '🏢',
            ],
            [
                'id' => 3,
                'title' => 'Review & Create',
                'description' => 'Review all information and send invitations',
                'icon' => '✅',
            ],
        ];

        $timezones = [
            'UTC' => 'UTC (Coordinated Universal Time)',
            'Africa/Accra' => 'Africa/Accra (GMT+0)',
            'Africa/Lagos' => 'Africa/Lagos (WAT, GMT+1)',
            'Africa/Cairo' => 'Africa/Cairo (EET, GMT+2)',
            'Africa/Johannesburg' => 'Africa/Johannesburg (SAST, GMT+2)',
            'Africa/Nairobi' => 'Africa/Nairobi (EAT, GMT+3)',
            'Europe/London' => 'Europe/London (GMT/BST)',
            'Europe/Paris' => 'Europe/Paris (CET, GMT+1)',
            'Europe/Berlin' => 'Europe/Berlin (CET, GMT+1)',
            'America/New_York' => 'America/New York (EST/EDT)',
            'America/Chicago' => 'America/Chicago (CST/CDT)',
            'America/Denver' => 'America/Denver (MST/MDT)',
            'America/Los_Angeles' => 'America/Los Angeles (PST/PDT)',
            'Asia/Dubai' => 'Asia/Dubai (GST, GMT+4)',
            'Asia/Kolkata' => 'Asia/Kolkata (IST, GMT+5:30)',
            'Asia/Singapore' => 'Asia/Singapore (SGT, GMT+8)',
            'Asia/Tokyo' => 'Asia/Tokyo (JST, GMT+9)',
            'Australia/Sydney' => 'Australia/Sydney (AEST, GMT+10)',
        ];

        return view('meetings.multi-company.create', [
            'rooms' => $rooms,
            'currentStep' => $step,
            'steps' => $steps,
            'timezones' => $timezones,
            'wizardData' => $sessionData,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $action = $request->input('action', 'next');
        $step = (int) $request->input('step', 1);

        // Cancel action
        if ($action === 'cancel') {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('meetings.index')->with('success', 'Meeting creation cancelled.');
        }

        // Save as draft from Step 1 or 2
        if ($action === 'save_draft') {
            return $this->saveAsDraft($request, $step);
        }

        // Normal step validation and navigation
        if ($step === 1) {
            $validated = $this->validateStep1($request);
            $sessionData = $request->session()->get(self::SESSION_KEY, []);
            $sessionData = array_merge($sessionData, $validated);

            // Handle agenda file upload if present
            if ($request->hasFile('agenda_file')) {
                $sessionData['agenda_file_path'] = $request->file('agenda_file')->store('meeting-agendas', 'public');
                $sessionData['agenda_file_name'] = $request->file('agenda_file')->getClientOriginalName();
            }

            $request->session()->put(self::SESSION_KEY, $sessionData);

            return redirect()->route('multi-company-meetings.create', ['step' => 2]);
        }

        if ($step === 2) {
            $validated = $this->validateStep2($request);
            $sessionData = $request->session()->get(self::SESSION_KEY, []);
            $sessionData['companies'] = $validated['companies'];

            $request->session()->put(self::SESSION_KEY, $sessionData);

            return redirect()->route('multi-company-meetings.create', ['step' => 3]);
        }

        if ($step === 3) {
            // Final step: Create Meeting & Send Invitations
            return $this->finalizeMeetingCreation($request);
        }

        return redirect()->route('multi-company-meetings.create', ['step' => 1]);
    }

    public function importCompanies(Request $request): RedirectResponse
    {
        $request->validate([
            'companies_file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:5120'],
        ]);

        $importer = app(CompanyImporter::class);
        $result = $importer->import($request->file('companies_file'));

        if (! $result['success']) {
            return back()->with('error', $result['error'] ?? 'Failed to import companies.');
        }

        $sessionData = $request->session()->get(self::SESSION_KEY, []);
        $existingCompanies = $sessionData['companies'] ?? [];

        // Merge imported companies with existing ones (avoid duplicates by email)
        $existingEmails = collect($existingCompanies)->pluck('email')->map(fn ($e) => strtolower(trim($e)))->toArray();

        foreach ($result['companies'] as $company) {
            $emailLower = strtolower(trim($company['email']));
            if (! in_array($emailLower, $existingEmails, true)) {
                $existingCompanies[] = $company;
                $existingEmails[] = $emailLower;
            }
        }

        $sessionData['companies'] = $existingCompanies;
        $request->session()->put(self::SESSION_KEY, $sessionData);

        $message = "Imported {$result['imported']} companies successfully.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} rows skipped (invalid or duplicate data).";
        }

        return redirect()->route('multi-company-meetings.create', ['step' => 2])
            ->with('success', $message);
    }

    private function validateStep1(Request $request): array
    {
        return $request->validate([
            'meeting_title' => ['required', 'string', 'max:180'],
            'host_company' => ['required', 'string', 'max:160'],
            'host_name' => ['required', 'string', 'max:120'],
            'host_email' => ['required', 'email', 'max:160'],
            'host_phone' => ['required', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:3000'],
            'meeting_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'timezone' => ['required', 'string', 'max:60'],
            'meeting_type' => ['required', Rule::in(['online'])],
            'who_can_join' => ['required', Rule::in(['invited_only', 'approved_link'])],
            'max_companies' => ['required', 'integer', 'min:1', 'max:100'],
            'allow_screen_share' => ['required', Rule::in(['host_only', 'presenters', 'all_approved'])],
            'document_sharing' => ['required', Rule::in(['host_only', 'approved_participants'])],
            'allow_recording' => ['required', Rule::in(['host', 'authorized_users', 'none'])],
            'chat_permissions' => ['required', Rule::in(['everyone', 'representatives_only', 'host_only'])],
            'participant_approval' => ['required', Rule::in(['automatic', 'host_approval'])],
            'waiting_room' => ['nullable', 'boolean'],
            'mute_on_entry' => ['nullable', 'boolean'],
            'agenda_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,txt', 'max:10240'],
        ]);
    }

    private function validateStep2(Request $request): array
    {
        $sessionData = $request->session()->get(self::SESSION_KEY, []);
        $maxCompanies = (int) ($sessionData['max_companies'] ?? 50);

        $validated = $request->validate([
            'companies' => ['required', 'array', 'min:1', "max:{$maxCompanies}"],
            'companies.*.name' => ['required', 'string', 'max:160'],
            'companies.*.email' => ['required', 'email', 'max:160'],
            'companies.*.phone' => ['nullable', 'string', 'max:40'],
            'companies.*.rep_name' => ['required', 'string', 'max:160'],
            'companies.*.rep_email' => ['required', 'email', 'max:160'],
            'companies.*.rep_department' => ['nullable', 'string', 'max:160'],
            'companies.*.invitation_message' => ['nullable', 'string', 'max:1000'],
        ]);

        // Duplicate prevention within the same meeting
        $namesSeen = [];
        $emailsSeen = [];
        $repEmailsSeen = [];

        foreach ($validated['companies'] as $index => $company) {
            $nameNorm = strtolower(trim($company['name']));
            $emailNorm = strtolower(trim($company['email']));
            $repEmailNorm = strtolower(trim($company['rep_email']));

            if (in_array($nameNorm, $namesSeen, true)) {
                throw ValidationException::withMessages([
                    "companies.{$index}.name" => "The company '{$company['name']}' has already been added to this meeting.",
                ]);
            }
            $namesSeen[] = $nameNorm;

            if (in_array($emailNorm, $emailsSeen, true)) {
                throw ValidationException::withMessages([
                    "companies.{$index}.email" => "The company email '{$company['email']}' has already been added to this meeting.",
                ]);
            }
            $emailsSeen[] = $emailNorm;

            if (in_array($repEmailNorm, $repEmailsSeen, true)) {
                throw ValidationException::withMessages([
                    "companies.{$index}.rep_email" => "Representative email '{$company['rep_email']}' is assigned to multiple companies.",
                ]);
            }
            $repEmailsSeen[] = $repEmailNorm;
        }

        // Handle company logo uploads
        if ($request->hasFile('companies')) {
            foreach ($request->file('companies') as $index => $files) {
                if (isset($files['logo']) && $files['logo']->isValid()) {
                    $validated['companies'][$index]['logo_path'] = $files['logo']->store('company-logos', 'public');
                }
            }
        }

        return $validated;
    }

    private function saveAsDraft(Request $request, int $currentStep): RedirectResponse
    {
        $sessionData = $request->session()->get(self::SESSION_KEY, []);

        if ($currentStep === 1) {
            // Validate minimal fields for draft
            $validated = $request->validate([
                'meeting_title' => ['required', 'string', 'max:180'],
                'host_company' => ['nullable', 'string', 'max:160'],
                'host_name' => ['nullable', 'string', 'max:120'],
                'host_email' => ['nullable', 'email', 'max:160'],
                'host_phone' => ['nullable', 'string', 'max:40'],
                'meeting_date' => ['nullable', 'date'],
                'start_time' => ['nullable'],
                'duration_minutes' => ['nullable', 'integer'],
            ]);
            $sessionData = array_merge($sessionData, $request->except(['_token', 'action']));
        } elseif ($currentStep === 2) {
            $sessionData['companies'] = $request->input('companies', $sessionData['companies'] ?? []);
        }

        $hostUser = auth()->user();
        $startDate = ! empty($sessionData['meeting_date']) && ! empty($sessionData['start_time'])
            ? Carbon::parse($sessionData['meeting_date'].' '.$sessionData['start_time'])
            : now()->addDay();
        $duration = (int) ($sessionData['duration_minutes'] ?? 60);
        $endDate = $startDate->copy()->addMinutes($duration);

        $meeting = DB::transaction(function () use ($sessionData, $hostUser, $startDate, $endDate) {
            $meeting = Meeting::create([
                'name' => $sessionData['host_name'] ?? ($hostUser?->name ?: 'Host'),
                'email' => $sessionData['host_email'] ?? ($hostUser?->email ?: 'host@example.com'),
                'phone' => $sessionData['host_phone'] ?? '0000000000',
                'company_name' => $sessionData['host_company'] ?? ($hostUser?->name ?: 'Host Company'),
                'meeting_topic' => $sessionData['meeting_title'] ?? 'Draft Multi-Company Meeting',
                'description' => $sessionData['description'] ?? null,
                'topic_category' => 'business_discussion',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'meeting_room_id' => $sessionData['meeting_room_id'] ?? null,
                'physical_location' => $sessionData['location'] ?? null,
                'status' => 'draft',
                'is_online' => true,
                'is_multi_company' => true,
                'meeting_type' => $sessionData['meeting_type'] ?? 'online',
                'session_provider' => 'internal',
                'host_user_id' => $hostUser?->id,
                'settings' => [
                    'timezone' => $sessionData['timezone'] ?? 'UTC',
                    'duration_minutes' => $duration,
                    'who_can_join' => $sessionData['who_can_join'] ?? 'invited_only',
                    'max_companies' => $sessionData['max_companies'] ?? 10,
                    'allow_screen_share' => $sessionData['allow_screen_share'] ?? 'host_only',
                    'document_sharing' => $sessionData['document_sharing'] ?? 'host_only',
                    'allow_recording' => ($sessionData['allow_recording'] ?? 'host') !== 'none',
                    'chat_permissions' => $sessionData['chat_permissions'] ?? 'everyone',
                    'participant_approval' => $sessionData['participant_approval'] ?? 'automatic',
                    'waiting_room' => (bool) ($sessionData['waiting_room'] ?? true),
                    'mute_on_entry' => (bool) ($sessionData['mute_on_entry'] ?? false),
                    'agenda_file_path' => $sessionData['agenda_file_path'] ?? null,
                    'agenda_file_name' => $sessionData['agenda_file_name'] ?? null,
                ],
            ]);

            // Save host company
            if (! empty($sessionData['host_company'])) {
                MeetingCompany::forName($meeting, $sessionData['host_company'], true);
            }

            // Save draft companies if available
            if (! empty($sessionData['companies'])) {
                foreach ($sessionData['companies'] as $c) {
                    if (! empty($c['name'])) {
                        $company = MeetingCompany::forName($meeting, $c['name']);
                        $company->update([
                            'email' => $c['email'] ?? null,
                            'phone' => $c['phone'] ?? null,
                            'contact_name' => $c['rep_name'] ?? null,
                            'contact_email' => $c['rep_email'] ?? null,
                            'contact_department' => $c['rep_department'] ?? null,
                            'company_agenda' => $c['invitation_message'] ?? null,
                            'status' => 'pending',
                            'invitation_status' => 'draft',
                        ]);
                    }
                }
            }

            return $meeting;
        });

        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('multi-company-meetings.show', $meeting)
            ->with('success', 'Meeting saved as draft. You can review and send invitations when ready.');
    }

    private function finalizeMeetingCreation(Request $request): RedirectResponse
    {
        $sessionData = $request->session()->get(self::SESSION_KEY, []);

        if (empty($sessionData['meeting_title']) || empty($sessionData['companies'])) {
            return redirect()->route('multi-company-meetings.create', ['step' => 1])
                ->with('error', 'Meeting session data expired. Please re-enter the meeting details.');
        }

        $hostUser = auth()->user();
        $startDate = Carbon::parse($sessionData['meeting_date'].' '.$sessionData['start_time']);
        $duration = (int) ($sessionData['duration_minutes'] ?? 60);
        $endDate = $startDate->copy()->addMinutes($duration);
        $meetingType = $sessionData['meeting_type'] ?? 'online';

        $invitationResults = [];

        $meeting = DB::transaction(function () use ($sessionData, $hostUser, $startDate, $endDate, $duration, $meetingType, &$invitationResults) {
            $meeting = Meeting::create([
                'name' => $sessionData['host_name'],
                'email' => $sessionData['host_email'],
                'phone' => $sessionData['host_phone'],
                'company_name' => $sessionData['host_company'],
                'meeting_topic' => $sessionData['meeting_title'],
                'description' => $sessionData['description'] ?? null,
                'topic_category' => 'business_discussion',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'meeting_room_id' => $sessionData['meeting_room_id'] ?? null,
                'physical_location' => $sessionData['location'] ?? null,
                'status' => 'scheduled',
                'is_online' => true,
                'is_multi_company' => true,
                'meeting_type' => $meetingType,
                'session_provider' => 'internal',
                'host_user_id' => $hostUser?->id,
                'settings' => [
                    'timezone' => $sessionData['timezone'] ?? 'UTC',
                    'duration_minutes' => $duration,
                    'who_can_join' => $sessionData['who_can_join'] ?? 'invited_only',
                    'max_companies' => $sessionData['max_companies'] ?? 10,
                    'allow_screen_share' => $sessionData['allow_screen_share'] ?? 'host_only',
                    'document_sharing' => $sessionData['document_sharing'] ?? 'host_only',
                    'allow_recording' => ($sessionData['allow_recording'] ?? 'host') !== 'none',
                    'recording_permission' => $sessionData['allow_recording'] ?? 'host',
                    'chat_permissions' => $sessionData['chat_permissions'] ?? 'everyone',
                    'participant_approval' => $sessionData['participant_approval'] ?? 'automatic',
                    'waiting_room' => (bool) ($sessionData['waiting_room'] ?? true),
                    'mute_on_entry' => (bool) ($sessionData['mute_on_entry'] ?? false),
                    'allow_participant_unmute' => true,
                    'allow_participant_camera' => true,
                    'allow_participant_hand' => true,
                    'agenda_file_path' => $sessionData['agenda_file_path'] ?? null,
                    'agenda_file_name' => $sessionData['agenda_file_name'] ?? null,
                ],
            ]);

            // Generate signed meeting room URL
            $meeting->update([
                'session_url' => URL::signedRoute('sessions.join', ['meeting' => $meeting]),
            ]);

            // Create Host Company
            $hostCompany = MeetingCompany::forName($meeting, $sessionData['host_company'], true);
            $hostCompany->update([
                'email' => $sessionData['host_email'],
                'phone' => $sessionData['host_phone'],
                'contact_name' => $sessionData['host_name'],
                'contact_email' => $sessionData['host_email'],
                'contact_phone' => $sessionData['host_phone'],
                'status' => 'accepted',
                'is_active' => true,
            ]);

            // Register Each Invited Company and Issue Secure Invitation
            foreach ($sessionData['companies'] as $cData) {
                $company = MeetingCompany::forName($meeting, $cData['name'], false);

                $company->update([
                    'email' => $cData['email'],
                    'phone' => $cData['phone'] ?? null,
                    'contact_name' => $cData['rep_name'],
                    'contact_email' => $cData['rep_email'],
                    'contact_phone' => $cData['phone'] ?? null,
                    'contact_department' => $cData['rep_department'] ?? null,
                    'company_agenda' => $cData['invitation_message'] ?? null,
                    'logo_path' => $cData['logo_path'] ?? null,
                    'status' => 'pending',
                    'is_active' => true,
                ]);

                // Create primary representative CompanyContact
                CompanyContact::create([
                    'meeting_company_id' => $company->id,
                    'name' => $cData['rep_name'],
                    'email' => $cData['rep_email'],
                    'phone' => $cData['phone'] ?? null,
                    'department' => $cData['rep_department'] ?? null,
                    'is_primary' => true,
                ]);

                // Pre-register representative as Attendee
                $attendee = Attendee::create([
                    'meeting_id' => $meeting->id,
                    'meeting_company_id' => $company->id,
                    'full_name' => $cData['rep_name'],
                    'email' => $cData['rep_email'],
                    'department' => $cData['rep_department'] ?? null,
                    'phone' => preg_replace('/\D+/', '', (string) ($cData['phone'] ?? '')) ?: '0000000000',
                    'invitation_status' => 'sent',
                    'requires_approval' => ($sessionData['participant_approval'] ?? 'automatic') === 'host_approval',
                ]);

                // Issue secure unpredictable token with expiry
                $tokenExpiry = $endDate->copy()->addDays(7);
                $company->issueInvitation($tokenExpiry);

                // Send email to contact_email using actual configured mail service
                $emailResult = $this->sendCompanyInvitationEmail($meeting, $company);

                $invitationResults[] = [
                    'company' => $company->name,
                    'email' => $company->contact_email,
                    'success' => $emailResult['success'],
                    'error' => $emailResult['error'] ?? null,
                ];
            }

            return $meeting;
        });

        // Forget wizard session
        $request->session()->forget(self::SESSION_KEY);

        $sentCount = collect($invitationResults)->where('success', true)->count();
        $failedCount = collect($invitationResults)->where('success', false)->count();

        $message = "Multi-Company Meeting '{$meeting->meeting_topic}' created successfully!";
        if ($sentCount > 0) {
            $message .= " {$sentCount} company invitation(s) sent.";
        }
        if ($failedCount > 0) {
            $message .= " {$failedCount} email(s) could not be delivered — check company statuses below to resend.";
        }

        return redirect()->route('multi-company-meetings.show', $meeting)
            ->with('success', $message)
            ->with('meeting_created', true)
            ->with('invitation_results', $invitationResults);
    }

    private function sendCompanyInvitationEmail(Meeting $meeting, MeetingCompany $company): array
    {
        if ($company->is_host_company) {
            return ['success' => true];
        }

        if (empty($company->contact_email)) {
            $company->update([
                'invitation_delivery_status' => 'failed',
                'invitation_failure_reason' => 'No contact email specified.',
            ]);

            return ['success' => false, 'error' => 'No contact email specified.'];
        }

        try {
            Mail::to($company->contact_email)->send(new CompanyInvitation($meeting, $company));

            $company->update([
                'invitation_delivery_status' => 'sent',
                'invitation_sent_at' => now(),
                'invitation_status' => 'sent',
                'invitation_failure_reason' => null,
            ]);

            return ['success' => true];
        } catch (\Throwable $e) {
            Log::warning("Company invitation email failed for {$company->contact_email}: ".$e->getMessage());

            $company->update([
                'invitation_delivery_status' => 'failed',
                'invitation_failure_reason' => $e->getMessage(),
                'invitation_retry_count' => $company->invitation_retry_count + 1,
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function show(Meeting $meeting): View
    {
        abort_unless($meeting->is_multi_company, 404);

        $meeting->load([
            'room',
            'companies' => fn ($query) => $query
                ->with(['attendees', 'contacts', 'memberships'])
                ->orderByDesc('is_host_company')
                ->orderBy('name'),
            'attendees.meetingCompany',
        ]);

        $companies = $meeting->companies;
        $hostCompany = $companies->firstWhere('is_host_company', true);
        $invitedCompanies = $companies->where('is_host_company', false);

        // Fetch live online participants
        $activeParticipants = OnlineSessionParticipant::with(['company', 'attendee'])
            ->where('meeting_id', $meeting->id)
            ->whereNull('left_at')
            ->where('status', OnlineSessionParticipant::STATUS_ADMITTED)
            ->get();

        $waitingParticipants = OnlineSessionParticipant::with(['company', 'attendee'])
            ->where('meeting_id', $meeting->id)
            ->whereNull('left_at')
            ->where('status', OnlineSessionParticipant::STATUS_WAITING)
            ->get();

        $allAttendees = $meeting->attendees;

        $stats = [
            'total_companies' => $invitedCompanies->count(),
            'accepted_companies' => $invitedCompanies->where('status', 'accepted')->count(),
            'declined_companies' => $invitedCompanies->where('status', 'declined')->count(),
            'pending_companies' => $invitedCompanies->where('status', 'pending')->count(),
            'total_attendees' => $allAttendees->count(),
            'accepted_attendees' => $allAttendees->where('invitation_status', 'accepted')->count(),
            'waiting_count' => $waitingParticipants->count(),
            'joined_count' => $activeParticipants->count(),
            'status' => $meeting->dashboardStatus(),
        ];

        $joinUrl = $meeting->joinUrl();
        $settings = $meeting->sessionSettings();

        return view('meetings.multi-company.show', compact(
            'meeting',
            'companies',
            'hostCompany',
            'invitedCompanies',
            'activeParticipants',
            'waitingParticipants',
            'allAttendees',
            'stats',
            'joinUrl',
            'settings'
        ));
    }

    public function resendInvitation(Meeting $meeting, MeetingCompany $company): RedirectResponse
    {
        abort_unless($company->meeting_id === $meeting->id, 404);
        abort_if($company->is_host_company, 403, 'The host company does not require an invitation.');

        // Re-issue token if missing or revoked
        if (blank($company->invitation_token) || $company->invitation_status === 'cancelled' || ($company->invitation_expires_at && $company->invitation_expires_at->isPast())) {
            $company->issueInvitation($meeting->end_date->copy()->addDays(7));
        }

        $result = $this->sendCompanyInvitationEmail($meeting, $company);

        if ($result['success']) {
            return back()->with('success', "Invitation re-sent successfully to {$company->contact_email}.");
        }

        return back()->with('error', 'Failed to re-send invitation: '.($result['error'] ?? 'Unknown email error'));
    }

    public function revokeInvitation(Meeting $meeting, MeetingCompany $company): RedirectResponse
    {
        abort_unless($company->meeting_id === $meeting->id, 404);
        abort_if($company->is_host_company, 403, 'The host company cannot be revoked.');

        $company->revokeInvitation();

        // Also mark company status
        $company->update([
            'status' => 'declined',
            'invitation_status' => 'cancelled',
        ]);

        return back()->with('success', "Invitation for '{$company->name}' has been revoked. Their invitation link is no longer valid.");
    }

    public function cancelMeeting(Meeting $meeting): RedirectResponse
    {
        abort_unless($meeting->is_multi_company, 404);

        $meeting->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->route('meetings.index')
            ->with('success', "Meeting '{$meeting->meeting_topic}' was cancelled.");
    }

    public function exportAttendance(Meeting $meeting): Response
    {
        abort_unless($meeting->is_multi_company, 404);

        $meeting->load(['companies.attendees', 'attendees.meetingCompany']);

        $filename = 'attendance-'.Str::slug($meeting->meeting_topic).'-'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $handle = fopen('php://memory', 'r+');
        // UTF-8 BOM
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($handle, ['Meeting Title', $meeting->meeting_topic]);
        fputcsv($handle, ['Meeting Code', $meeting->meeting_code]);
        fputcsv($handle, ['Date & Time', $meeting->start_date->format('Y-m-d H:i').' - '.$meeting->end_date->format('H:i')]);
        fputcsv($handle, ['Host Company', $meeting->company_name]);
        fputcsv($handle, ['Status', ucfirst($meeting->dashboardStatus())]);
        fputcsv($handle, []);

        fputcsv($handle, [
            'Company Name',
            'Is Host Company',
            'Participant Name',
            'Email',
            'Phone',
            'Department / Role',
            'Invitation Status',
            'Checked In At',
            'Checked Out At',
        ]);

        foreach ($meeting->companies as $company) {
            if ($company->attendees->isEmpty()) {
                fputcsv($handle, [
                    $company->name,
                    $company->is_host_company ? 'Yes' : 'No',
                    $company->contact_name ?: '—',
                    $company->contact_email ?: $company->email,
                    $company->contact_phone ?: $company->phone ?: '—',
                    $company->contact_department ?: '—',
                    ucfirst($company->invitation_status),
                    '—',
                    '—',
                ]);
            } else {
                foreach ($company->attendees as $att) {
                    fputcsv($handle, [
                        $company->name,
                        $company->is_host_company ? 'Yes' : 'No',
                        $att->full_name,
                        $att->email,
                        $att->phone,
                        $att->department ?: '—',
                        ucfirst($att->invitation_status),
                        $att->checked_in_at ? $att->checked_in_at->format('Y-m-d H:i:s') : '—',
                        $att->checked_out_at ? $att->checked_out_at->format('Y-m-d H:i:s') : '—',
                    ]);
                }
            }
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, $headers);
    }
}
