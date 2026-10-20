<?php

namespace App\Http\Controllers;

use App\Mail\OnlineSessionVerificationCode;
use App\Models\Attendee;
use App\Models\DocumentAccessLog;
use App\Models\DocumentShare;
use App\Models\Meeting;
use App\Models\OnlineSessionChatMessage;
use App\Models\OnlineSessionEmailVerification;
use App\Models\OnlineSessionParticipant;
use App\Models\OnlineSessionRecording;
use App\Models\OnlineSessionSetting;
use App\Models\OnlineSessionTranscriptSegment;
use App\Models\ScreenShare;
use App\Models\Signal;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Document\DocumentValidationService;
use App\Services\SessionAssistantDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OnlineSessionController extends Controller
{
    private const PARTICIPANT_TIMEOUT_SECONDS = 90;

    public function quickStart()
    {
        $user = auth()->user();
        $now = now();

        $meeting = Meeting::create([
            'meeting_code' => 'AYK-'.strtoupper(Str::random(6)),
            'name' => $user->name ?? 'Host',
            'email' => $user->email ?? 'host@aykonmeet.local',
            'phone' => '0000000000',
            'company_name' => null,
            'meeting_topic' => 'Instant meeting Ã‚Â· '.$now->format('M j, g:i A'),
            'start_date' => $now,
            'end_date' => $now->copy()->addHour(),
            'status' => 'in_progress',
            'started_at' => $now,
            'is_online' => true,
            'host_user_id' => $user?->id,
            'session_provider' => 'internal',
            'session_url' => null,
        ]);

        return redirect(URL::signedRoute('sessions.join', ['meeting' => $meeting]));
    }

    public function index()
    {
        $now = now();

        $liveSessions = Meeting::with('room')
            ->withCount('attendees')
            ->where('is_online', true)
            ->where('status', 'in_progress')
            ->orderBy('start_date')
            ->get();

        $upcomingSessions = Meeting::with('room')
            ->withCount('attendees')
            ->where('is_online', true)
            ->where('status', '!=', 'cancelled')
            ->where('start_date', '>=', $now)
            ->whereDate('start_date', '<=', $now->copy()->addDays(14))
            ->orderBy('start_date')
            ->get();

        $recentSessions = Meeting::with('room')
            ->withCount('attendees')
            ->where('is_online', true)
            ->whereIn('status', ['completed', 'cancelled'])
            ->latest('start_date')
            ->limit(8)
            ->get();

        $stats = [
            'live' => $liveSessions->count(),
            'upcoming' => $upcomingSessions->count(),
            'total' => Meeting::where('is_online', true)->count(),
            'this_week' => Meeting::where('is_online', true)
                ->whereBetween('start_date', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
                ->count(),
        ];

        return view('sessions.index', compact('liveSessions', 'upcomingSessions', 'recentSessions', 'stats'));
    }

    public function createSession(Request $request, Meeting $meeting)
    {
        return back();
    }

    public function destroySession(Meeting $meeting)
    {
        return back();
    }

    public function joinSession(Request $request, Meeting $meeting)
    {
        abort_unless($meeting->is_online, 404);

        // Check meeting join state using the meeting's inviteeJoinState method
        $joinState = $meeting->inviteeJoinState();

        if ($joinState === 'closed') {
            // If it's an AJAX request, return JSON error
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'ended',
                    'message' => 'This online session has ended.',
                ], 410);
            }

            // For regular browser requests, show the meeting ended page
            return view('meetings.ended', ['meeting' => $meeting]);
        }

        if (auth()->check()
            && $meeting->host_user_id === null
            && strtolower(auth()->user()->email) === strtolower($meeting->email)) {
            $meeting->update(['host_user_id' => auth()->id()]);
        }
        $isHost = auth()->check()
            && $meeting->host_user_id !== null
            && auth()->id() === $meeting->host_user_id;

        $invitedAttendee = $this->resolveInvitedAttendee($request, $meeting);
        $isHost = $isHost && ! $invitedAttendee;

        $urls = [
            'send_code' => URL::signedRoute('sessions.participants.send-code', ['meeting' => $meeting]),
            'join' => URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]),
            'guest_join' => URL::signedRoute('sessions.participants.guest-join', ['meeting' => $meeting]),
            'heartbeat' => URL::signedRoute('sessions.participants.heartbeat', ['meeting' => $meeting]),
            'leave' => URL::signedRoute('sessions.participants.leave', ['meeting' => $meeting]),
            'status' => URL::signedRoute('sessions.participants.status', ['meeting' => $meeting]),
            'waiting_room' => URL::signedRoute('sessions.participants.waiting-room', ['meeting' => $meeting]),
            'admit' => URL::signedRoute('sessions.participants.admit', ['meeting' => $meeting]),
            'admit_all' => URL::signedRoute('sessions.participants.admit-all', ['meeting' => $meeting]),
            'admit_company' => URL::signedRoute('sessions.participants.admit-company', ['meeting' => $meeting]),
            'reject' => URL::signedRoute('sessions.participants.reject', ['meeting' => $meeting]),
            'remove' => URL::signedRoute('sessions.participants.remove', ['meeting' => $meeting]),
            'co_host' => URL::signedRoute('sessions.participants.co-host', ['meeting' => $meeting]),
            'screen_share_check' => URL::signedRoute('sessions.screen-share', ['meeting' => $meeting]),
            'screen_share_start' => URL::signedRoute('sessions.screen-share.start', ['meeting' => $meeting]),
            'screen_share_stop' => URL::signedRoute('sessions.screen-share.stop', ['meeting' => $meeting]),
            'screen_share_status' => URL::signedRoute('sessions.screen-share.status', ['meeting' => $meeting]),
            'mute' => URL::signedRoute('sessions.participants.mute', ['meeting' => $meeting]),
            'signal_send' => URL::signedRoute('sessions.signals.send', ['meeting' => $meeting]),
            'chat_send' => URL::signedRoute('sessions.chat.send', ['meeting' => $meeting]),
            'hand' => URL::signedRoute('sessions.hand', ['meeting' => $meeting]),
            'recording_start' => URL::signedRoute('sessions.recording.start', ['meeting' => $meeting]),
            'recording_finish' => URL::signedRoute('sessions.recording.finish', ['meeting' => $meeting]),
            'transcript_store' => URL::signedRoute('sessions.transcript.store', ['meeting' => $meeting]),
            'documents_upload' => URL::signedRoute('documents.upload', ['meeting' => $meeting]),
            'documents_list' => URL::signedRoute('documents.list', ['meeting' => $meeting]),
            'documents_preview_base' => route('documents.preview', ['meeting' => $meeting, 'document' => '__DOC_ID__']),
            'documents_download_base' => route('documents.download', ['meeting' => $meeting, 'document' => '__DOC_ID__']),
            'documents_update_base' => route('documents.update', ['meeting' => $meeting, 'document' => '__DOC_ID__']),
            'documents_revoke_base' => route('documents.revoke', ['meeting' => $meeting, 'document' => '__DOC_ID__']),
        ];
        $isHybrid = $meeting->isHybrid();
        $hybridLocation = $isHybrid
            ? ($meeting->physical_location ?: $meeting->room?->location ?: $meeting->room?->name)
            : null;
        $hybridSummary = $isHybrid ? $this->hybridRoomSummaryData($meeting) : null;
        if ($isHybrid) {
            $urls['hybrid_summary'] = route('meetings.hybrid-room-summary', $meeting);
        }

        $directJoin = null;
        if ($invitedAttendee) {
            [$participant, $token] = $this->createSessionParticipant($meeting, $invitedAttendee, $invitedAttendee->full_name, false);
            $directJoin = $this->participantJoinPayload($meeting, $participant, $token);
        }

        return view('sessions.join', compact('meeting', 'urls', 'isHost', 'isHybrid', 'hybridLocation', 'hybridSummary', 'directJoin'));
    }

    /**
     * Create a session participant, applying co-host designation and the
     * waiting-room gate for the meeting.
     *
     * @return array{0: OnlineSessionParticipant, 1: string}
     */
    private function createSessionParticipant(Meeting $meeting, ?Attendee $attendee, string $displayName, bool $isHost, array $assignment = []): array
    {
        $token = Str::random(64);
        $coHostName = $isHost ? null : $meeting->setting('co_host');
        $isCoHost = ! $isHost && is_string($coHostName) && $coHostName !== ''
            && strcasecmp(trim($coHostName), trim($displayName)) === 0;
        $requiresAdmission = ! $isHost && ! $isCoHost && ((bool) $meeting->setting('waiting_room') || (bool) $attendee?->requires_approval);
        $mutedOnEntry = ! $isHost && ! $isCoHost && ! $requiresAdmission && (bool) $meeting->setting('mute_on_entry');

        $company = $assignment['company'] ?? $attendee?->meetingCompany;
        $companyId = $assignment['company_id'] ?? $company?->id;
        $role = $assignment['role'] ?? ($isHost
            ? OnlineSessionParticipant::ROLE_HOST
            : ($isCoHost ? OnlineSessionParticipant::ROLE_CO_HOST : OnlineSessionParticipant::ROLE_PARTICIPANT));
        $source = $assignment['assignment_source'] ?? ($attendee !== null
            ? OnlineSessionParticipant::SOURCE_INVITE
            : ($isHost ? OnlineSessionParticipant::SOURCE_HOST : OnlineSessionParticipant::SOURCE_GUEST));
        $userId = $assignment['user_id'] ?? null;
        if ($userId === null && $attendee !== null && $attendee->email) {
            $userId = User::query()
                ->whereRaw('LOWER(email) = ?', [strtolower($attendee->email)])
                ->value('id');
        }

        $participant = OnlineSessionParticipant::create([
            'meeting_id' => $meeting->id,
            'attendee_id' => $attendee?->id,
            'user_id' => $userId,
            'company_id' => $companyId,
            'peer_id' => (string) Str::uuid(),
            'token_hash' => hash('sha256', $token),
            'display_name' => $displayName,
            'role' => $role,
            'assignment_source' => $source,
            'company_verified_at' => $companyId !== null ? now() : null,
            'is_host' => $isHost,
            'is_co_host' => $isCoHost,
            'co_host_permissions' => $isCoHost ? $meeting->coHostPermissions() : null,
            'status' => $requiresAdmission
                ? OnlineSessionParticipant::STATUS_WAITING
                : OnlineSessionParticipant::STATUS_ADMITTED,
            'is_muted_by_host' => $mutedOnEntry,
            'joined_at' => $requiresAdmission ? null : now(),
            'requested_at' => $requiresAdmission ? now() : null,
            'admitted_at' => $requiresAdmission ? null : now(),
            'last_seen_at' => now(),
        ]);

        $this->supersedeEarlierSessions($participant);
        if ($attendee && $participant->isAdmitted()) {
            $attendee->update(['checked_out_at' => null]);
        }

        return [$participant, $token];
    }

    /**
     * @return array<string, mixed>
     */
    private function participantJoinPayload(Meeting $meeting, OnlineSessionParticipant $participant, string $token): array
    {
        return [
            'peer_id' => $participant->peer_id,
            'token' => $token,
            'is_host' => (bool) $participant->is_host,
            'is_co_host' => (bool) $participant->is_co_host,
            'waiting' => $participant->isWaiting(),
            'is_muted' => (bool) $participant->is_muted_by_host,
            'can_record' => $this->canRecord($participant),
            'permissions' => $this->viewerPermissions($meeting, $participant),
            'waiting_room_message' => $meeting->setting('waiting_room_message'),
            'participants' => $participant->isWaiting()
                ? []
                : $this->activeParticipants($meeting, $participant->peer_id)->values(),
        ];
    }

    /**
     * Permission flags for the live room, resolved for a specific participant.
     *
     * @return array<string, bool>
     */
    private function viewerPermissions(Meeting $meeting, OnlineSessionParticipant $participant): array
    {
        $media = $participant->actsAsHost()
            ? ['chat' => true, 'unmute' => true, 'camera' => true, 'screen_share' => true, 'hand' => true, 'record' => true]
            : $meeting->participantPermissions();

        if ($participant->actsAsHost()) {
            $media['record'] = $this->canRecord($participant);
        }

        $capabilities = [];
        foreach (array_keys(OnlineSessionParticipant::CO_HOST_PERMISSIONS) as $capability) {
            $capabilities[$capability] = $participant->hasCoHostPermission($capability);
        }

        return $media + $capabilities;
    }

    /**
     * Resolve an invited attendee from a signed, tokenised invitation link.
     */
    private function resolveInvitedAttendee(Request $request, Meeting $meeting): ?Attendee
    {
        $token = (string) $request->query('invite', '');

        if ($token === '' || strlen($token) !== 64) {
            return null;
        }

        return Attendee::query()
            ->where('meeting_id', $meeting->id)
            ->where('invitation_token', $token)
            ->where('invitation_status', 'accepted')
            ->first();
    }

    public function hybridRoomSummary(Meeting $meeting)
    {
        abort_unless($meeting->isHybrid(), 404);

        return response()->json($this->hybridRoomSummaryData($meeting));
    }

    private function hybridRoomSummaryData(Meeting $meeting): array
    {
        return [
            'online_count' => $this->activeParticipants($meeting)->count(),
            'in_person_count' => $meeting->attendees()
                ->where('attendance_mode', 'in_person')
                ->whereNotNull('checked_in_at')
                ->whereNull('checked_out_at')
                ->count(),
        ];
    }

    public function hybridAttendance(Meeting $meeting)
    {
        abort_unless($meeting->isHybrid(), 404);
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        return response()->json($this->hybridAttendanceData($meeting));
    }

    public function hybridAttendanceData(Meeting $meeting): array
    {
        $attendees = $meeting->attendees()->orderBy('full_name')->get([
            'id', 'full_name', 'email', 'attendance_mode',
        ]);
        $online = $attendees->where('attendance_mode', 'online')->values();
        $inPerson = $attendees->where('attendance_mode', 'in_person')->values();
        $notResponded = $attendees->whereNull('attendance_mode')->values();

        return [
            'online_count' => $online->count(),
            'in_person_count' => $inPerson->count(),
            'not_responded_count' => $notResponded->count(),
            'online' => $online->map(fn (Attendee $attendee) => ['name' => $attendee->full_name])->values(),
            'in_person' => $inPerson->map(fn (Attendee $attendee) => ['name' => $attendee->full_name])->values(),
            'not_responded' => $notResponded->map(fn (Attendee $attendee) => ['name' => $attendee->full_name])->values(),
        ];
    }

    public function saveHybridRsvpByEmail(Request $request, Meeting $meeting)
    {
        abort_unless($meeting->isHybrid(), 404);
        $this->ensureRsvpCanChange($meeting);
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'verification_code' => ['required', 'digits:6'],
            'attendance_mode' => ['required', 'in:online,in_person'],
        ]);
        $attendee = $meeting->attendees()
            ->whereRaw('LOWER(email) = ?', [strtolower($data['email'])])
            ->first();
        abort_unless($attendee, 422, 'The verification code is invalid or expired. Request a new code and try again.');
        $verified = $this->consumeEmailVerificationCode($meeting, $attendee, $data['verification_code']);
        abort_unless($verified, 422, 'The verification code is invalid or expired. Request a new code and try again.');
        $attendee->update(['attendance_mode' => $data['attendance_mode']]);

        return response()->json([
            'message' => 'Your attendance choice has been saved.',
            'attendance_mode' => $attendee->attendance_mode,
        ]);
    }

    public function sendParticipantVerificationCode(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $rateLimitKey = implode(':', [
            'online-session-email-verification',
            $request->ip(),
            $meeting->id,
            hash('sha256', strtolower($data['email'])),
        ]);
        abort_if(
            RateLimiter::tooManyAttempts($rateLimitKey, 5),
            429,
            'Too many verification codes requested. Please try again in a minute.'
        );
        RateLimiter::hit($rateLimitKey, 60);

        $attendee = $meeting->attendees()
            ->whereRaw('LOWER(email) = ?', [strtolower($data['email'])])
            ->first();

        if ($attendee) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            OnlineSessionEmailVerification::updateOrCreate(
                ['meeting_id' => $meeting->id, 'attendee_id' => $attendee->id],
                [
                    'code_hash' => $this->verificationCodeHash($code),
                    'expires_at' => now()->addMinutes(10),
                    'attempts' => 0,
                ],
            );

            Mail::to($attendee->email)->send(
                new OnlineSessionVerificationCode($meeting, $attendee, $code)
            );
        }

        return response()->json([
            'message' => 'If this email is invited, a verification code has been sent.',
        ]);
    }

    public function joinGuest(Request $request, Meeting $meeting)
    {
        abort_if($meeting->isHybrid(), 404);
        $this->ensureJoinable($meeting);

        if ($meeting->inviteeJoinState() === 'closed') {
            return response()->json([
                'status' => 'ended',
                'message' => 'This meeting has ended and can no longer be joined.',
            ], 410);
        }

        $isHost = auth()->check()
            && $meeting->host_user_id !== null
            && auth()->id() === $meeting->host_user_id;
        abort_if($isHost, 403, 'Hosts join with their account email.');

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:80'],
        ]);
        $displayName = trim(preg_replace('/\s+/u', ' ', strip_tags($data['display_name'])) ?? '');
        abort_if($displayName === '', 422, 'Enter your name so the host knows who is joining.');

        [$participant, $token] = $this->createSessionParticipant($meeting, null, $displayName, false);

        return response()->json($this->participantJoinPayload($meeting, $participant, $token));
    }

    public function joinParticipant(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);

        // Check meeting join state using the meeting's inviteeJoinState method
        $joinState = $meeting->inviteeJoinState();

        if ($joinState === 'closed') {
            return response()->json([
                'status' => 'ended',
                'message' => 'This meeting has ended and can no longer be joined.',
            ], 410);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'verification_code' => ['nullable', 'digits:6'],
        ]);
        $isHost = auth()->id() !== null && auth()->id() === $meeting->host_user_id;
        $attendee = $meeting->attendees()
            ->whereRaw('LOWER(email) = ?', [strtolower($data['email'])])
            ->first();
        $hostEmailMatches = $isHost
            && strtolower($data['email']) === strtolower(auth()->user()->email);

        abort_unless($hostEmailMatches || ($attendee && $data['verification_code']), 403,
            'Verify your invited email address before joining.');
        if ($meeting->isHybrid() && $attendee) {
            if ($attendee->attendance_mode === 'in_person') {
                $this->ensureRsvpCanChange($meeting);
            }
        }

        if (! $hostEmailMatches) {
            $verified = $this->consumeEmailVerificationCode($meeting, $attendee, $data['verification_code']);

            abort_unless($verified, 422, 'The verification code is invalid or expired. Request a new code and try again.');
        }

        if ($attendee) {
            $attendee->update([
                'checked_in_at' => $attendee->checked_in_at ?? now(),
                'checked_out_at' => null,
                'attendance_mode' => 'online',
            ]);
        }

        $displayName = $attendee?->full_name ?? (auth()->check() ? auth()->user()->name : 'Guest');
        [$participant, $token] = $this->createSessionParticipant($meeting, $attendee, $displayName, $isHost);

        // If waiting room is enabled and participant is not host/co-host, put them in waiting room
        $requiresAdmission = ! $isHost && $meeting->setting('waiting_room');
        if ($requiresAdmission && ! $isHost) {
            $participant->update([
                'status' => OnlineSessionParticipant::STATUS_WAITING,
                'requested_at' => now(),
            ]);
        }

        return response()->json($this->participantJoinPayload($meeting, $participant, $token));
    }

    /**
     * Status endpoint polled by participants waiting in the waiting room.
     */
    public function participantStatus(Request $request, Meeting $meeting)
    {
        // Check if meeting has ended
        if ($meeting->ended_at !== null || in_array($meeting->status, ['completed', 'cancelled'], true)) {
            return response()->json([
                'status' => 'ended',
                'message' => 'This meeting has ended.',
            ], 410);
        }

        $participant = $this->authorizedParticipant($request, $meeting, true);

        if ($participant->isAdmitted()) {
            $participant->update(['last_seen_at' => now()]);

            return response()->json($this->participantJoinPayload($meeting, $participant, (string) $request->input('token')));
        }

        if ($participant->isRemoved()) {
            return response()->json([
                'status' => 'removed',
                'message' => 'The host did not admit you to this meeting.',
            ], 403);
        }

        $participant->update(['last_seen_at' => now()]);

        return response()->json([
            'status' => 'waiting',
            'waiting_room_message' => $meeting->setting('waiting_room_message'),
        ]);
    }

    public function heartbeat(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);

        // Check if meeting has ended - return ended status
        if ($meeting->ended_at !== null || in_array($meeting->status, ['completed', 'cancelled'], true)) {
            return response()->json([
                'status' => 'ended',
                'message' => 'This meeting has ended.',
            ], 410);
        }

        $participant = $this->authorizedParticipant($request, $meeting, true);

        if ($participant->isRemoved()) {
            return response()->json([
                'status' => 'removed',
                'message' => 'The host removed you from this meeting.',
            ], 403);
        }

        if ($participant->isWaiting()) {
            return response()->json([
                'status' => 'waiting',
                'waiting_room_message' => $meeting->setting('waiting_room_message'),
            ]);
        }

        $this->expireStaleParticipants($meeting, $participant->peer_id);
        $participant->update(['last_seen_at' => now()]);

        return response()->json([
            'status' => 'admitted',
            'is_muted' => $participant->is_muted_by_host,
            'is_co_host' => (bool) $participant->is_co_host,
            'permissions' => $this->viewerPermissions($meeting, $participant),
            'participants' => $this->activeParticipants($meeting, $participant->peer_id),
            'waiting_count' => $participant->actsAsHost()
                ? $this->waitingParticipants($meeting)->count()
                : 0,
            'signals' => Signal::where('meeting_id', $meeting->id)
                ->where('to_peer', $participant->peer_id)
                ->where('id', '>', $request->integer('after_signal_id'))
                ->orderBy('id')->limit(100)
                ->get(['id', 'from_peer', 'type', 'payload']),
            'messages' => OnlineSessionChatMessage::with('participant:id,display_name')
                ->where('meeting_id', $meeting->id)
                ->where('id', '>', $request->integer('after_chat_id'))
                ->orderBy('id')->limit(100)
                ->get(['id', 'participant_id', 'message', 'created_at']),
            'recordings' => $this->activeRecordings($meeting),
        ]);
    }

    /**
     * Waiting-room roster for the host and permitted co-hosts.
     */
    public function waitingRoom(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);
        $this->authorizedControl($request, $meeting, 'manage_waiting_room');

        return response()->json([
            'waiting' => $this->waitingParticipants($meeting)->values(),
        ]);
    }

    public function admitParticipant(Request $request, Meeting $meeting)
    {
        $this->authorizedControl($request, $meeting, 'manage_waiting_room');
        $data = $request->validate(['peer_id' => ['required', 'uuid']]);
        $participant = $this->participant($meeting, $data['peer_id']);
        $this->admit($meeting, $participant);

        return response()->json(['ok' => true]);
    }

    public function admitAllParticipants(Request $request, Meeting $meeting)
    {
        $this->authorizedControl($request, $meeting, 'manage_waiting_room');
        $count = 0;
        foreach ($this->waitingParticipants($meeting) as $waiting) {
            $this->admit($meeting, $this->participant($meeting, $waiting['peer_id']));
            $count++;
        }

        return response()->json(['ok' => true, 'admitted' => $count]);
    }

    public function admitCompany(Request $request, Meeting $meeting)
    {
        $this->authorizedControl($request, $meeting, 'manage_waiting_room');
        $data = $request->validate(['company_id' => ['required', 'integer']]);
        $count = 0;
        foreach ($this->waitingParticipants($meeting) as $waiting) {
            if ((int) ($waiting['company_id'] ?? 0) === (int) $data['company_id']) {
                $this->admit($meeting, $this->participant($meeting, $waiting['peer_id']));
                $count++;
            }
        }

        return response()->json(['ok' => true, 'admitted' => $count]);
    }

    public function rejectParticipant(Request $request, Meeting $meeting)
    {
        $this->authorizedControl($request, $meeting, 'manage_waiting_room');
        $data = $request->validate(['peer_id' => ['required', 'uuid']]);
        $participant = $this->participant($meeting, $data['peer_id']);
        $this->remove($participant);

        return response()->json(['ok' => true]);
    }

    public function removeParticipant(Request $request, Meeting $meeting)
    {
        $actor = $this->authorizedControl($request, $meeting, 'remove_participants');
        $data = $request->validate(['peer_id' => ['required', 'uuid']]);
        $participant = $this->participant($meeting, $data['peer_id']);
        abort_if($participant->is_host, 403, 'The host cannot be removed from the meeting.');
        abort_if($actor && $actor->id === $participant->id, 403, 'You cannot remove yourself from the meeting.');
        $this->remove($participant);

        return response()->json(['ok' => true]);
    }

    public function promoteCoHost(Request $request, Meeting $meeting)
    {
        $this->authorizedControl($request, $meeting, 'promote_co_hosts');
        $data = $request->validate([
            'peer_id' => ['required', 'uuid'],
            'is_co_host' => ['required', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(OnlineSessionParticipant::CO_HOST_PERMISSIONS))],
        ]);
        $participant = $this->participant($meeting, $data['peer_id']);
        abort_if($participant->is_host, 403, 'The host already has full control.');

        $permissions = array_values(array_intersect(
            array_keys(OnlineSessionParticipant::CO_HOST_PERMISSIONS),
            $data['permissions'] ?? [],
        ));

        $participant->update([
            'is_co_host' => $data['is_co_host'],
            'co_host_permissions' => $data['is_co_host'] ? $permissions : null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function checkScreenShare(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting);
        abort_unless(
            $participant->actsAsHost() || $meeting->setting('allow_screen_share'),
            403,
            'The host has disabled screen sharing for participants.',
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Start screen sharing for a participant.
     */
    public function startScreenShare(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting);

        abort_unless(
            $participant->actsAsHost() || $meeting->setting('allow_screen_share'),
            403,
            'The host has disabled screen sharing for participants.',
        );

        // End any existing active screen share for this participant
        ScreenShare::where('meeting_id', $meeting->id)
            ->where('participant_id', $participant->id)
            ->where('status', ScreenShare::STATUS_ACTIVE)
            ->update(['status' => ScreenShare::STATUS_ENDED, 'ended_at' => now()]);

        $data = $request->validate([
            'share_type' => ['required', Rule::in(ScreenShare::TYPES)],
        ]);

        $screenShare = ScreenShare::create([
            'meeting_id' => $meeting->id,
            'participant_id' => $participant->id,
            'share_type' => $data['share_type'],
            'status' => ScreenShare::STATUS_ACTIVE,
        ]);

        // Broadcast screen share start to other participants via signal
        $this->broadcastScreenShareUpdate($meeting, $screenShare, 'started');

        return response()->json([
            'ok' => true,
            'screen_share_id' => $screenShare->id,
            'share_type' => $screenShare->share_type,
            'sharer_peer_id' => $participant->peer_id,
            'sharer_name' => $participant->display_name,
        ]);
    }

    /**
     * Stop screen sharing for a participant.
     */
    public function stopScreenShare(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting);

        $screenShare = ScreenShare::where('meeting_id', $meeting->id)
            ->where('participant_id', $participant->id)
            ->where('status', ScreenShare::STATUS_ACTIVE)
            ->first();

        if ($screenShare) {
            $screenShare->end();
            $this->broadcastScreenShareUpdate($meeting, $screenShare, 'stopped');
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Get current active screen share in the meeting.
     */
    public function getScreenShare(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting, true);

        $screenShare = ScreenShare::where('meeting_id', $meeting->id)
            ->where('status', ScreenShare::STATUS_ACTIVE)
            ->with('participant:id,peer_id,display_name')
            ->first();

        if (! $screenShare) {
            return response()->json(['active' => false]);
        }

        return response()->json([
            'active' => true,
            'screen_share_id' => $screenShare->id,
            'share_type' => $screenShare->share_type,
            'sharer_peer_id' => $screenShare->participant->peer_id,
            'sharer_name' => $screenShare->participant->display_name,
            'started_at' => $screenShare->started_at?->toIso8601String(),
        ]);
    }

    /**
     * Broadcast screen share update to all participants.
     */
    private function broadcastScreenShareUpdate(Meeting $meeting, ScreenShare $screenShare, string $action): void
    {
        $participants = OnlineSessionParticipant::where('meeting_id', $meeting->id)
            ->whereNull('left_at')
            ->where('status', OnlineSessionParticipant::STATUS_ADMITTED)
            ->get(['id', 'peer_id']);

        $payload = [
            'screen_share_id' => $screenShare->id,
            'action' => $action,
            'share_type' => $screenShare->share_type,
            'sharer_peer_id' => $screenShare->participant->peer_id,
            'sharer_name' => $screenShare->participant->display_name,
        ];

        foreach ($participants as $p) {
            if ($p->peer_id !== $screenShare->participant->peer_id) {
                Signal::create([
                    'meeting_id' => $meeting->id,
                    'from_peer' => 'system',
                    'to_peer' => $p->peer_id,
                    'type' => 'screen-share-update',
                    'payload' => $payload,
                ]);
            }
        }
    }

    /**
     * Upload a document to the meeting.
     */
    public function uploadDocument(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:51200'],
            'share_scope' => ['required', Rule::in(DocumentShare::SCOPES)],
            'shared_with_companies' => ['nullable', 'array'],
            'shared_with_companies.*' => ['integer', 'exists:meeting_companies,id'],
            'shared_with_participants' => ['nullable', 'array'],
            'shared_with_participants.*' => ['integer', 'exists:online_session_participants,id'],
            'allow_download' => ['boolean'],
            'allow_preview' => ['boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'retention_policy' => ['nullable', Rule::in(DocumentShare::RETENTION_POLICIES)],
            'encrypted_e2e' => ['boolean'],
        ]);

        $file = $request->file('file');

        // Validate file using DocumentValidationService
        $validationService = app(DocumentValidationService::class);

        try {
            $fileInfo = $validationService->validate($file);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        // Generate secure stored name
        $storedName = $fileInfo['stored_name'];
        $path = $file->storeAs('meeting-documents/'.$meeting->id, $storedName, 'local');

        $document = DocumentShare::create([
            'meeting_id' => $meeting->id,
            'uploader_id' => $participant->id,
            'original_name' => $fileInfo['original_name'],
            'stored_name' => $storedName,
            'mime_type' => $fileInfo['mime_type'],
            'extension' => $fileInfo['extension'],
            'file_size' => $fileInfo['file_size'],
            'storage_path' => $path,
            'storage_disk' => 'local',
            'share_scope' => $data['share_scope'],
            'shared_with_companies' => $data['shared_with_companies'] ?? null,
            'shared_with_participants' => $data['shared_with_participants'] ?? null,
            'allow_download' => $data['allow_download'] ?? true,
            'allow_preview' => $data['allow_preview'] ?? true,
            'status' => DocumentShare::STATUS_ACTIVE,
            'expires_at' => $data['expires_at'] ?? null,
            'retention_policy' => $data['retention_policy'] ?? DocumentShare::RETENTION_KEEP,
            'encrypted_e2e' => $data['encrypted_e2e'] ?? false,
        ]);

        // Log upload
        DocumentAccessLog::create([
            'document_share_id' => $document->id,
            'participant_id' => $participant->id,
            'action' => DocumentAccessLog::ACTION_UPLOAD,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Broadcast document upload to participants
        $this->broadcastDocumentUpdate($meeting, $document, 'uploaded');

        return response()->json([
            'ok' => true,
            'document' => [
                'id' => $document->id,
                'original_name' => $document->original_name,
                'mime_type' => $document->mime_type,
                'file_size' => $document->file_size,
                'extension' => $document->extension,
                'share_scope' => $document->share_scope,
                'allow_download' => $document->allow_download,
                'allow_preview' => $document->allow_preview,
                'expires_at' => $document->expires_at?->toIso8601String(),
                'retention_policy' => $document->retention_policy,
                'encrypted_e2e' => $document->encrypted_e2e,
                'uploader_name' => $participant->display_name,
                'uploader_company' => $participant->company?->name,
                'created_at' => $document->created_at->toIso8601String(),
                'preview_url' => $document->getPreviewUrl(),
                'download_url' => $document->getDownloadUrl(),
            ],
        ], 201);
    }

    /**
     * List documents in the meeting.
     */
    public function listDocuments(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting, true);

        $documents = DocumentShare::where('meeting_id', $meeting->id)
            ->where('status', DocumentShare::STATUS_ACTIVE)
            ->with('uploader:id,peer_id,display_name,company_id')
            ->with('uploader.company:id,name,logo_path')
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(fn (DocumentShare $doc) => $doc->isAccessibleBy($participant))
            ->map(function (DocumentShare $doc) use ($participant) {
                return [
                    'id' => $doc->id,
                    'original_name' => $doc->original_name,
                    'mime_type' => $doc->mime_type,
                    'file_size' => $doc->file_size,
                    'extension' => $doc->extension,
                    'share_scope' => $doc->share_scope,
                    'allow_download' => $doc->canDownload($participant),
                    'allow_preview' => $doc->canPreview($participant),
                    'expires_at' => $doc->expires_at?->toIso8601String(),
                    'retention_policy' => $doc->retention_policy,
                    'encrypted_e2e' => $doc->encrypted_e2e,
                    'uploader_name' => $doc->uploader->display_name,
                    'uploader_company' => $doc->uploader->company?->name,
                    'created_at' => $doc->created_at->toIso8601String(),
                    'preview_url' => $doc->canPreview($participant) ? $doc->getPreviewUrl() : null,
                    'download_url' => $doc->canDownload($participant) ? $doc->getDownloadUrl() : null,
                    'can_manage' => $doc->canManage($participant),
                ];
            })
            ->values();

        return response()->json(['documents' => $documents]);
    }

    /**
     * Preview a document (inline view).
     */
    public function previewDocument(Request $request, Meeting $meeting, DocumentShare $document)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting, true);

        abort_unless($document->meeting_id === $meeting->id, 404);
        abort_unless($document->isAccessible(), 410, 'This document has expired or been revoked.');
        abort_unless($document->canPreview($participant), 403, 'You do not have permission to preview this document.');

        // Log preview access
        DocumentAccessLog::create([
            'document_share_id' => $document->id,
            'participant_id' => $participant->id,
            'action' => DocumentAccessLog::ACTION_PREVIEW,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $disk = Storage::disk($document->storage_disk);
        abort_unless($disk->exists($document->storage_path), 404);

        $mimeType = $document->mime_type;
        $content = $disk->get($document->storage_path);

        return response($content, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$document->original_name.'"',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Download a document.
     */
    public function downloadDocument(Request $request, Meeting $meeting, DocumentShare $document)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting, true);

        abort_unless($document->meeting_id === $meeting->id, 404);
        abort_unless($document->isAccessible(), 410, 'This document has expired or been revoked.');
        abort_unless($document->canDownload($participant), 403, 'You do not have permission to download this document.');

        // Log download
        DocumentAccessLog::create([
            'document_share_id' => $document->id,
            'participant_id' => $participant->id,
            'action' => DocumentAccessLog::ACTION_DOWNLOAD,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $disk = Storage::disk($document->storage_disk);
        abort_unless($disk->exists($document->storage_path), 404);

        return $disk->download($document->storage_path, $document->original_name);
    }

    /**
     * Update document sharing permissions.
     */
    public function updateDocumentShare(Request $request, Meeting $meeting, DocumentShare $document)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting);
        $actor = $this->authorizedControl($request, $meeting, 'remove_participants'); // Using existing permission
        abort_unless($actor === null || $actor->id === $document->uploader_id, 403, 'Only the uploader or host can modify document sharing.');

        abort_unless($document->meeting_id === $meeting->id, 404);
        abort_unless($document->isActive(), 422, 'Cannot modify a revoked or deleted document.');

        $data = $request->validate([
            'share_scope' => ['required', Rule::in(DocumentShare::SCOPES)],
            'shared_with_companies' => ['nullable', 'array'],
            'shared_with_companies.*' => ['integer', 'exists:meeting_companies,id'],
            'shared_with_participants' => ['nullable', 'array'],
            'shared_with_participants.*' => ['integer', 'exists:online_session_participants,id'],
            'allow_download' => ['boolean'],
            'allow_preview' => ['boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'retention_policy' => ['nullable', Rule::in(DocumentShare::RETENTION_POLICIES)],
            'encrypted_e2e' => ['boolean'],
        ]);

        $document->update([
            'share_scope' => $data['share_scope'],
            'shared_with_companies' => $data['shared_with_companies'] ?? null,
            'shared_with_participants' => $data['shared_with_participants'] ?? null,
            'allow_download' => $data['allow_download'] ?? true,
            'allow_preview' => $data['allow_preview'] ?? true,
            'expires_at' => $data['expires_at'] ?? $document->expires_at,
            'retention_policy' => $data['retention_policy'] ?? $document->retention_policy,
            'encrypted_e2e' => $data['encrypted_e2e'] ?? $document->encrypted_e2e,
        ]);

        // Log share update
        DocumentAccessLog::create([
            'document_share_id' => $document->id,
            'participant_id' => $participant->id ?? $actor?->id,
            'action' => DocumentAccessLog::ACTION_SHARE_UPDATE,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $data,
        ]);

        $this->broadcastDocumentUpdate($meeting, $document, 'share_updated');

        return response()->json(['ok' => true, 'document' => $document]);
    }

    /**
     * Revoke/remove a document.
     */
    public function revokeDocument(Request $request, Meeting $meeting, DocumentShare $document)
    {
        $this->ensureJoinable($meeting);
        $actor = $this->authorizedControl($request, $meeting, 'remove_participants');
        abort_unless($actor === null || $actor->id === $document->uploader_id, 403, 'Only the uploader or host can revoke a document.');

        abort_unless($document->meeting_id === $meeting->id, 404);

        $document->revoke($actor ?? $participant ?? null);

        // Log revocation
        DocumentAccessLog::create([
            'document_share_id' => $document->id,
            'participant_id' => $actor?->id ?? $participant?->id,
            'action' => DocumentAccessLog::ACTION_REVOKE,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Also delete the file
        $document->deleteFile();

        $this->broadcastDocumentUpdate($meeting, $document, 'revoked');

        return response()->json(['ok' => true]);
    }

    /**
     * Broadcast recording update to all participants.
     */
    private function broadcastRecordingUpdate(Meeting $meeting, OnlineSessionRecording $recording, string $action): void
    {
        $participants = OnlineSessionParticipant::where('meeting_id', $meeting->id)
            ->whereNull('left_at')
            ->where('status', OnlineSessionParticipant::STATUS_ADMITTED)
            ->get(['id', 'peer_id']);

        $payload = [
            'recording_id' => $recording->id,
            'action' => $action,
            'recorder_name' => $recording->participant->display_name,
        ];

        foreach ($participants as $p) {
            Signal::create([
                'meeting_id' => $meeting->id,
                'from_peer' => 'system',
                'to_peer' => $p->peer_id,
                'type' => 'recording-update',
                'payload' => $payload,
            ]);
        }
    }

    public function setParticipantMute(Request $request, Meeting $meeting)
    {
        $actor = $this->authorizedControl($request, $meeting, 'mute_participants');
        $data = $request->validate([
            'peer_id' => ['required', 'uuid'],
            'muted' => ['required', 'boolean'],
        ]);
        $participant = $this->participant($meeting, $data['peer_id']);
        abort_if($participant->is_host, 403, 'The host cannot be muted.');
        abort_if($actor && ! $actor->is_host && $participant->actsAsHost(), 403, 'Only the host can mute a co-host.');
        abort_if($actor && $actor->id === $participant->id, 403, 'You cannot mute yourself.');
        $participant->update(['is_muted_by_host' => $data['muted']]);

        return response()->json(['ok' => true]);
    }

    public function leaveParticipant(Request $request, Meeting $meeting)
    {
        $participant = $this->authorizedParticipant($request, $meeting);

        // Clear token hash to prevent rejoining with the same token
        $participant->update([
            'left_at' => now(),
            'hand_raised' => false,
            'token_hash' => null,
        ]);

        if (! $this->samePersonStillPresent($participant)) {
            $participant->attendee?->update(['checked_out_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    public function sendSignal(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting);
        $data = $request->validate([
            'to_peer' => ['required', 'uuid'],
            'type' => ['required', 'in:offer,answer,ice-candidate'],
            'payload' => ['required', 'array'],
        ]);
        abort_if(strlen(json_encode($data['payload'], JSON_THROW_ON_ERROR)) > 65536, 413);
        abort_unless(
            OnlineSessionParticipant::where('meeting_id', $meeting->id)
                ->where('peer_id', $data['to_peer'])
                ->whereNull('left_at')
                ->where('last_seen_at', '>=', now()->subSeconds(self::PARTICIPANT_TIMEOUT_SECONDS))
                ->exists(),
            404
        );

        Signal::create([
            'meeting_id' => $meeting->id,
            'from_peer' => $participant->peer_id,
            'to_peer' => $data['to_peer'],
            'type' => $data['type'],
            'payload' => $data['payload'],
        ]);

        return response()->json(['ok' => true]);
    }

    public function sendChatMessage(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting);
        abort_unless(
            $participant->actsAsHost() || $meeting->setting('allow_chat'),
            403,
            'The host has turned off chat for participants.',
        );
        $data = $request->validate(['message' => ['required', 'string', 'regex:/\S/', 'max:2000']]);
        $message = OnlineSessionChatMessage::create([
            'meeting_id' => $meeting->id,
            'participant_id' => $participant->id,
            'message' => trim($data['message']),
        ]);

        return response()->json(['id' => $message->id], 201);
    }

    public function setRaisedHand(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting);
        $data = $request->validate([
            'raised' => ['required', 'boolean'],
            'question' => ['nullable', 'string', 'max:500'],
        ]);
        abort_unless(
            ! $data['raised'] || $participant->actsAsHost() || $meeting->setting('allow_participant_hand'),
            403,
            'The host has turned off raised hands for participants.',
        );
        $participant->update(['hand_raised' => $data['raised']]);

        return response()->json(['ok' => true]);
    }

    public function storeTranscriptSegment(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting);
        $data = $request->validate([
            'text' => ['required', 'string', 'regex:/\S/', 'max:3000'],
        ]);
        $segment = OnlineSessionTranscriptSegment::create([
            'meeting_id' => $meeting->id,
            'participant_id' => $participant->id,
            'text' => trim($data['text']),
            'spoken_at' => now(),
        ]);

        return response()->json(['id' => $segment->id], 201);
    }

    public function startRecording(Request $request, Meeting $meeting)
    {
        $this->ensureJoinable($meeting);
        $participant = $this->authorizedParticipant($request, $meeting);
        abort_unless($this->canRecord($participant), 403, 'Recording is disabled for your account or this session.');
        $recording = OnlineSessionRecording::create([
            'meeting_id' => $meeting->id,
            'participant_id' => $participant->id,
            'started_at' => now(),
        ]);

        // Log audit event
        AuditLogService::recordingStarted($recording);

        // Broadcast recording started to all participants
        $this->broadcastRecordingUpdate($meeting, $recording, 'started');

        return response()->json(['recording_id' => $recording->id], 201);
    }

    public function finishRecording(Request $request, Meeting $meeting)
    {
        $participant = $this->authorizedParticipant($request, $meeting, true);
        $data = $request->validate([
            'recording_id' => ['required', 'integer'],
            'recording' => ['required', 'file', 'mimetypes:video/webm,video/mp4,video/x-matroska', 'max:512000'],
            'duration_seconds' => ['required', 'integer', 'min:1'],
        ]);
        $recording = OnlineSessionRecording::where('meeting_id', $meeting->id)
            ->where('participant_id', $participant->id)
            ->where('status', 'recording')
            ->findOrFail($data['recording_id']);
        $file = $request->file('recording');
        $path = $file->store('session-recordings/'.$meeting->id, 'local');
        if (! $path) {
            $recording->update(['status' => 'failed', 'ended_at' => now()]);

            return response()->json(['message' => 'The recording could not be saved to session storage.'], 500);
        }
        $recording->update([
            'storage_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'duration_seconds' => $data['duration_seconds'],
            'status' => 'ready',
            'ended_at' => now(),
        ]);

        // Log audit event
        AuditLogService::recordingStopped($recording);

        // Broadcast recording stopped to all participants
        $this->broadcastRecordingUpdate($meeting, $recording, 'stopped');

        return response()->json(['ok' => true]);
    }

    public function sessionReport(Meeting $meeting, SessionAssistantDraft $assistant)
    {
        abort_unless($meeting->is_online, 404);
        $isAdmin = auth()->user()->isAdmin();
        $isHostWithPermission = OnlineSessionSetting::current()->downloads_for_hosts
            && $meeting->host_user_id === auth()->id();
        abort_unless($isAdmin || $isHostWithPermission, 403);
        $participants = OnlineSessionParticipant::with('attendee.meetingCompany')
            ->where('meeting_id', $meeting->id)
            ->orderBy('joined_at')
            ->get();
        $recordings = OnlineSessionRecording::with('participant')
            ->where('meeting_id', $meeting->id)
            ->where('status', 'ready')
            ->orderByDesc('started_at')
            ->get();
        $messages = OnlineSessionChatMessage::with('participant:id,display_name')
            ->where('meeting_id', $meeting->id)
            ->orderBy('id')
            ->get();
        $transcript = OnlineSessionTranscriptSegment::with('participant:id,display_name')
            ->where('meeting_id', $meeting->id)
            ->orderBy('spoken_at')
            ->get();
        $assistantDraft = $assistant->generate($transcript);

        return view('sessions.report', compact('meeting', 'participants', 'recordings', 'messages', 'transcript', 'assistantDraft'));
    }

    public function downloadRecording(Meeting $meeting, OnlineSessionRecording $recording)
    {
        abort_unless($recording->meeting_id === $meeting->id && $recording->status === 'ready', 404);
        $isAdmin = auth()->user()->isAdmin();
        $isHostWithPermission = OnlineSessionSetting::current()->downloads_for_hosts
            && $meeting->host_user_id === auth()->id();
        abort_unless($isAdmin || $isHostWithPermission, 403);
        $disk = Storage::disk('local');
        abort_unless($recording->storage_path && $disk->exists($recording->storage_path), 404);

        return $disk->download($recording->storage_path, 'session-'.$meeting->meeting_code.'-'.$recording->id.'.webm');
    }

    public function deleteRecording(Meeting $meeting, OnlineSessionRecording $recording)
    {
        abort_unless($recording->meeting_id === $meeting->id, 404);
        if ($recording->storage_path
            && Storage::disk('local')->exists($recording->storage_path)
            && ! Storage::disk('local')->delete($recording->storage_path)) {
            return back()->with('error', 'The recording file could not be deleted. Try again.');
        }
        $recording->delete();

        return back()->with('success', 'Session recording deleted.');
    }

    private function ensureJoinable(Meeting $meeting): void
    {
        abort_unless($meeting->is_online, 404);
        abort_if(in_array($meeting->status, ['cancelled', 'completed'], true), 410);
    }

    private function canRecord(OnlineSessionParticipant $participant): bool
    {
        $settings = OnlineSessionSetting::current();
        if (! $settings->recording_enabled) {
            return false;
        }

        if ($participant->is_host) {
            return auth()->check()
                && auth()->id() === $participant->meeting->host_user_id
                && auth()->user()->can_record_sessions;
        }

        return $settings->allow_attendee_recording
            && (! auth()->check() || auth()->user()->can_record_sessions);
    }

    private function verificationCodeHash(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    private function ensureRsvpCanChange(Meeting $meeting): void
    {
        abort_unless($meeting->status === 'scheduled' && $meeting->started_at === null, 409,
            'Attendance choices can only be changed before the meeting starts.');
    }

    private function consumeEmailVerificationCode(Meeting $meeting, $attendee, string $code): bool
    {
        return DB::transaction(function () use ($meeting, $attendee, $code) {
            $verification = OnlineSessionEmailVerification::query()
                ->where('meeting_id', $meeting->id)
                ->where('attendee_id', $attendee->id)
                ->lockForUpdate()
                ->first();

            if (! $verification
                || $verification->expires_at->isPast()
                || $verification->attempts >= 5) {
                return false;
            }

            if (! hash_equals($verification->code_hash, $this->verificationCodeHash($code))) {
                $verification->increment('attempts');

                return false;
            }

            $verification->delete();

            return true;
        });
    }

    private function participant(Meeting $meeting, string $peerId): OnlineSessionParticipant
    {
        return OnlineSessionParticipant::where('meeting_id', $meeting->id)
            ->where('peer_id', $peerId)
            ->firstOrFail();
    }

    private function authorizedParticipant(Request $request, Meeting $meeting, bool $allowLeft = false): OnlineSessionParticipant
    {
        $data = $request->validate([
            'peer_id' => ['required', 'uuid'],
            'token' => ['required', 'string', 'size:64'],
        ]);
        $participant = $this->participant($meeting, $data['peer_id']);
        abort_if((! $allowLeft && $participant->left_at !== null) || ! $participant->token_hash
            || ! hash_equals($participant->token_hash, hash('sha256', $data['token'])), 403);

        return $participant;
    }

    /**
     * Authorize a host or delegated co-host for a control action.
     *
     * Returns the acting participant, or null when the authenticated meeting
     * host performs the action.
     */
    private function authorizedControl(Request $request, Meeting $meeting, string $permission): ?OnlineSessionParticipant
    {
        if (auth()->check() && $meeting->host_user_id !== null && auth()->id() === $meeting->host_user_id) {
            return null;
        }

        if (! $request->filled('actor_peer_id') || ! $request->filled('actor_token')) {
            abort(403, 'You do not have permission for that action.');
        }

        $data = $request->validate([
            'actor_peer_id' => ['required', 'uuid'],
            'actor_token' => ['required', 'string', 'size:64'],
        ]);
        $actor = $this->participant($meeting, $data['actor_peer_id']);
        abort_if(! $actor->token_hash
            || ! hash_equals($actor->token_hash, hash('sha256', $data['actor_token'])), 403);
        abort_unless(
            $actor->hasCoHostPermission($permission),
            403,
            'You do not have permission for that action.',
        );

        return $actor;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function waitingParticipants(Meeting $meeting)
    {
        $hostCompany = $meeting->isMultiCompany()
            ? ($meeting->companies()->where('is_host_company', true)->value('name') ?: $meeting->company_name)
            : null;

        return OnlineSessionParticipant::with(['attendee.meetingCompany', 'company'])
            ->where('meeting_id', $meeting->id)
            ->where('status', OnlineSessionParticipant::STATUS_WAITING)
            ->whereNull('left_at')
            ->orderBy('requested_at')
            ->get()
            ->map(function (OnlineSessionParticipant $participant) use ($hostCompany) {
                $company = $participant->company ?? $participant->attendee?->meetingCompany;

                return [
                    'peer_id' => $participant->peer_id,
                    'display_name' => $participant->display_name,
                    'requested_at' => optional($participant->requested_at)->toIso8601String(),
                    'company_id' => $company?->id,
                    'company_name' => $company?->name ?? ($participant->is_host ? $hostCompany : 'Guests'),
                ];
            });
    }

    private function admit(Meeting $meeting, OnlineSessionParticipant $participant): void
    {
        if (! $participant->isWaiting()) {
            return;
        }

        $participant->update([
            'status' => OnlineSessionParticipant::STATUS_ADMITTED,
            'admitted_at' => now(),
            'joined_at' => now(),
            'last_seen_at' => now(),
            'is_muted_by_host' => (bool) $meeting->setting('mute_on_entry'),
        ]);
    }

    private function remove(OnlineSessionParticipant $participant): void
    {
        $participant->update([
            'status' => OnlineSessionParticipant::STATUS_REMOVED,
            'removed_at' => now(),
            'left_at' => $participant->left_at ?? now(),
            'hand_raised' => false,
        ]);
        $participant->attendee?->update(['checked_out_at' => now()]);
    }

    private function activeParticipants(Meeting $meeting, ?string $exceptPeer = null)
    {
        $hostCompany = null;
        if ($meeting->isMultiCompany()) {
            $hostCompany = $meeting->companies()
                ->where('is_host_company', true)
                ->value('name') ?: ($meeting->company_name ?: 'Host company');
        }

        return OnlineSessionParticipant::with(['attendee.meetingCompany', 'company'])
            ->where('meeting_id', $meeting->id)
            ->whereNull('left_at')
            ->where('status', OnlineSessionParticipant::STATUS_ADMITTED)
            ->where('last_seen_at', '>=', now()->subSeconds(self::PARTICIPANT_TIMEOUT_SECONDS))
            ->when($exceptPeer, fn ($query) => $query->where('peer_id', '!=', $exceptPeer))
            ->orderBy('created_at')
            ->get(['id', 'peer_id', 'attendee_id', 'company_id', 'display_name', 'is_host', 'is_co_host', 'is_muted_by_host', 'hand_raised'])
            ->map(function (OnlineSessionParticipant $participant) use ($hostCompany) {
                $company = $participant->company ?? $participant->attendee?->meetingCompany;

                return [
                    'peer_id' => $participant->peer_id,
                    'display_name' => $participant->display_name,
                    'is_host' => (bool) $participant->is_host,
                    'is_co_host' => (bool) $participant->is_co_host,
                    'is_muted_by_host' => (bool) $participant->is_muted_by_host,
                    'hand_raised' => (bool) $participant->hand_raised,
                    'company_id' => $company?->id,
                    'company_name' => $company?->name
                        ?? ($participant->is_host ? $hostCompany : 'Guests'),
                    'company_logo' => $company?->logo_path,
                ];
            });
    }

    private function activeRecordings(Meeting $meeting)
    {
        OnlineSessionRecording::where('meeting_id', $meeting->id)
            ->where('status', 'recording')
            ->where('started_at', '<', now()->subHours(12))
            ->update(['status' => 'abandoned', 'ended_at' => now()]);

        return OnlineSessionRecording::with('participant:id,display_name')
            ->where('meeting_id', $meeting->id)
            ->where('status', 'recording')
            ->orderBy('started_at')
            ->get();
    }

    private function expireStaleParticipants(Meeting $meeting, string $exceptPeer): void
    {
        $staleParticipants = OnlineSessionParticipant::where('meeting_id', $meeting->id)
            ->whereNull('left_at')
            ->where('status', OnlineSessionParticipant::STATUS_ADMITTED)
            ->where('peer_id', '!=', $exceptPeer)
            ->where('last_seen_at', '<', now()->subSeconds(self::PARTICIPANT_TIMEOUT_SECONDS))
            ->get();

        foreach ($staleParticipants as $participant) {
            $this->markParticipantLeft($participant, $participant->last_seen_at);
        }
    }

    private function markParticipantLeft(OnlineSessionParticipant $participant, $leftAt): void
    {
        $participant->update(['left_at' => $leftAt, 'hand_raised' => false]);
        if (! $this->samePersonStillPresent($participant)) {
            $participant->attendee?->update(['checked_out_at' => $leftAt]);
        }
    }

    /**
     * A refresh creates a new peer. Close the earlier live session for that
     * same person so they are not listed, or shown on video, twice.
     */
    private function supersedeEarlierSessions(OnlineSessionParticipant $participant): void
    {
        $this->samePersonQuery($participant)->get()->each(function (OnlineSessionParticipant $earlier) {
            $earlier->update([
                'left_at' => now(),
                'hand_raised' => false,
                'token_hash' => null,
            ]);
        });
    }

    private function samePersonStillPresent(OnlineSessionParticipant $participant): bool
    {
        return $this->samePersonQuery($participant)->exists();
    }

    private function samePersonQuery(OnlineSessionParticipant $participant)
    {
        return OnlineSessionParticipant::query()
            ->where('meeting_id', $participant->meeting_id)
            ->whereKeyNot($participant->id)
            ->whereNull('left_at')
            ->where(function ($query) use ($participant) {
                if ($participant->attendee_id) {
                    $query->orWhere('attendee_id', $participant->attendee_id);
                }
                if ($participant->user_id) {
                    $query->orWhere('user_id', $participant->user_id);
                }
                if ($participant->is_host) {
                    $query->orWhere('is_host', true);
                }
                if (! $participant->attendee_id && ! $participant->is_host) {
                    $query->orWhere(function ($guests) use ($participant) {
                        $guests->whereNull('attendee_id')
                            ->where('is_host', false)
                            ->whereRaw('LOWER(display_name) = ?', [strtolower(trim($participant->display_name))]);
                    });
                }
            });
    }
}
