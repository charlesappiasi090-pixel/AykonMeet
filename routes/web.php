<?php

use App\Http\Controllers\AdminToolsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyImportController;
use App\Http\Controllers\CompanyInvitationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MultiCompanyMeetingController;
use App\Http\Controllers\OnlineSessionController;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

Route::get('/online-session', function () {
    return view('webrtc.meeting');
});
Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/join', function (Request $request) {
    $code = $request->query('code', '');
    $meeting = Meeting::where('meeting_code', strtoupper($code))->first();

    if (! $meeting) {
        return redirect()->route('landing')
            ->withInput(['code' => strtoupper($code)])
            ->with('error', 'We could not find a meeting with that code. Check it and try again.');
    }

    if (! $meeting->is_online) {
        return redirect()->route('meetings.show', $meeting->meeting_code);
    }

    return redirect(URL::signedRoute('sessions.join', ['meeting' => $meeting]));
})->name('meetings.join');

Route::get('/meetings/{code}', [MeetingController::class, 'show'])->name('meetings.show');
Route::get('/meetings/{meeting}/hybrid-room-summary', [OnlineSessionController::class, 'hybridRoomSummary'])
    ->middleware('throttle:60,1')->name('meetings.hybrid-room-summary');
Route::get('/meetings/{meeting}/hybrid-attendance', [OnlineSessionController::class, 'hybridAttendance'])
    ->middleware(['auth', 'throttle:60,1'])->name('meetings.hybrid-attendance');
Route::post('/meetings/{meeting}/hybrid-rsvp', [OnlineSessionController::class, 'saveHybridRsvpByEmail'])
    ->middleware('throttle:10,1,hybrid-rsvp:')->name('meetings.hybrid-rsvp');
Route::get('/meetings/{meeting}/join-session', [OnlineSessionController::class, 'joinSession'])
    ->middleware('signed')
    ->name('sessions.join');
Route::post('/meetings/{meeting}/session-participants/send-code', [OnlineSessionController::class, 'sendParticipantVerificationCode'])
    ->middleware(['signed', 'throttle:30,1'])->name('sessions.participants.send-code');
Route::post('/meetings/{meeting}/session-participants', [OnlineSessionController::class, 'joinParticipant'])
    ->middleware(['signed', 'throttle:10,1'])->name('sessions.participants.join');
Route::post('/meetings/{meeting}/session-participants/heartbeat', [OnlineSessionController::class, 'heartbeat'])
    ->middleware('signed')->name('sessions.participants.heartbeat');
Route::post('/meetings/{meeting}/session-participants/mute', [OnlineSessionController::class, 'setParticipantMute'])
    ->middleware('signed')->name('sessions.participants.mute');
Route::post('/meetings/{meeting}/session-participants/leave', [OnlineSessionController::class, 'leaveParticipant'])
    ->middleware('signed')->name('sessions.participants.leave');
Route::post('/meetings/{meeting}/session-participants/status', [OnlineSessionController::class, 'participantStatus'])
    ->middleware('signed')->name('sessions.participants.status');
Route::post('/meetings/{meeting}/session-participants/waiting-room', [OnlineSessionController::class, 'waitingRoom'])
    ->middleware('signed')->name('sessions.participants.waiting-room');
Route::post('/meetings/{meeting}/session-participants/admit', [OnlineSessionController::class, 'admitParticipant'])
    ->middleware('signed')->name('sessions.participants.admit');
Route::post('/meetings/{meeting}/session-participants/admit-all', [OnlineSessionController::class, 'admitAllParticipants'])
    ->middleware('signed')->name('sessions.participants.admit-all');
Route::post('/meetings/{meeting}/session-participants/admit-company', [OnlineSessionController::class, 'admitCompany'])
    ->middleware('signed')->name('sessions.participants.admit-company');
Route::post('/meetings/{meeting}/session-participants/reject', [OnlineSessionController::class, 'rejectParticipant'])
    ->middleware('signed')->name('sessions.participants.reject');
Route::post('/meetings/{meeting}/session-participants/remove', [OnlineSessionController::class, 'removeParticipant'])
    ->middleware('signed')->name('sessions.participants.remove');
Route::post('/meetings/{meeting}/session-participants/co-host', [OnlineSessionController::class, 'promoteCoHost'])
    ->middleware('signed')->name('sessions.participants.co-host');
Route::post('/meetings/{meeting}/session-screen-share', [OnlineSessionController::class, 'checkScreenShare'])
    ->middleware('signed')->name('sessions.screen-share');
Route::post('/meetings/{meeting}/session-screen-share/start', [OnlineSessionController::class, 'startScreenShare'])
    ->middleware('signed')->name('sessions.screen-share.start');
Route::post('/meetings/{meeting}/session-screen-share/stop', [OnlineSessionController::class, 'stopScreenShare'])
    ->middleware('signed')->name('sessions.screen-share.stop');
Route::get('/meetings/{meeting}/session-screen-share/status', [OnlineSessionController::class, 'getScreenShare'])
    ->middleware('signed')->name('sessions.screen-share.status');
Route::post('/meetings/{meeting}/session-signals', [OnlineSessionController::class, 'sendSignal'])
    ->middleware('signed')->name('sessions.signals.send');
Route::post('/meetings/{meeting}/session-chat', [OnlineSessionController::class, 'sendChatMessage'])
    ->middleware(['signed', 'throttle:60,1'])->name('sessions.chat.send');
Route::post('/meetings/{meeting}/session-hand', [OnlineSessionController::class, 'setRaisedHand'])
    ->middleware('signed')->name('sessions.hand');
Route::post('/meetings/{meeting}/session-transcript', [OnlineSessionController::class, 'storeTranscriptSegment'])
    ->middleware(['signed', 'throttle:60,1'])->name('sessions.transcript.store');
Route::post('/meetings/{meeting}/documents', [OnlineSessionController::class, 'uploadDocument'])
    ->middleware(['signed', 'throttle:30,1'])->name('documents.upload');
Route::get('/meetings/{meeting}/documents', [OnlineSessionController::class, 'listDocuments'])
    ->middleware('signed')->name('documents.list');
Route::get('/meetings/{meeting}/documents/{document}/preview', [OnlineSessionController::class, 'previewDocument'])
    ->middleware('signed')->name('documents.preview');
Route::get('/meetings/{meeting}/documents/{document}/download', [OnlineSessionController::class, 'downloadDocument'])
    ->middleware('signed')->name('documents.download');
Route::patch('/meetings/{meeting}/documents/{document}', [OnlineSessionController::class, 'updateDocumentShare'])
    ->middleware('signed')->name('documents.update');
Route::delete('/meetings/{meeting}/documents/{document}', [OnlineSessionController::class, 'revokeDocument'])
    ->middleware('signed')->name('documents.revoke');
Route::post('/meetings/{meeting}/session-recordings', [OnlineSessionController::class, 'startRecording'])
    ->middleware('signed')->name('sessions.recording.start');
Route::post('/meetings/{meeting}/session-recordings/upload', [OnlineSessionController::class, 'finishRecording'])
    ->middleware('signed')->name('sessions.recording.finish');

Route::get('/kiosk/{code}', [MeetingController::class, 'kiosk'])->name('kiosk.show');
Route::post('/meetings/{meeting}/kiosk-check-in', [MeetingController::class, 'kioskCheckIn'])->name('kiosk.check-in');

Route::prefix('invitation/{attendee:invitation_token}')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('/', [InvitationController::class, 'show'])->name('invitations.show');
        Route::post('/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
        Route::post('/decline', [InvitationController::class, 'decline'])->name('invitations.decline');
        Route::get('/confirmed', [InvitationController::class, 'confirmed'])->name('invitations.confirmed');
        Route::get('/lobby', [InvitationController::class, 'lobby'])->name('invitations.lobby');
        Route::get('/enter', [InvitationController::class, 'enter'])->name('invitations.enter');
        Route::get('/state', [InvitationController::class, 'state'])->name('invitations.state');
        Route::post('/colleagues', [InvitationController::class, 'inviteColleague'])->name('invitations.colleague');
    });

Route::get('/company-invitation/{token}', [CompanyInvitationController::class, 'show'])
    ->middleware('throttle:60,1')->name('company-invitations.show');
Route::post('/company-invitation/{token}', [CompanyInvitationController::class, 'verify'])
    ->middleware('throttle:10,1')->name('company-invitations.verify');
Route::post('/company-invitation/{token}/decline', [CompanyInvitationController::class, 'decline'])
    ->middleware('throttle:10,1')->name('company-invitations.decline');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/meetings/{meeting}/session-recordings/{recording}', [OnlineSessionController::class, 'downloadRecording'])
    ->middleware('auth')
    ->name('sessions.recordings.download');
Route::get('/meetings/{meeting}/session-report', [OnlineSessionController::class, 'sessionReport'])
    ->middleware('auth')
    ->name('sessions.report');
Route::post('/meetings/{meeting}/start', [MeetingController::class, 'start'])
    ->middleware('auth')
    ->name('meetings.start');
Route::post('/meetings/{meeting}/end', [MeetingController::class, 'end'])
    ->middleware('auth')
    ->name('meetings.end');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [MeetingController::class, 'rooms'])->name('admin.dashboard');
    Route::get('/admin/users', [AdminToolsController::class, 'users'])->name('users.index');
    Route::get('/admin/reports', [AdminToolsController::class, 'reports'])->name('reports.index');
    Route::get('/admin/expenses', [AdminToolsController::class, 'expenses'])->name('expenses.index');
    Route::get('/admin/notifications', [AdminToolsController::class, 'notifications'])->name('notifications.index');
    Route::get('/admin/settings', [AdminToolsController::class, 'settings'])->name('settings.index');
    Route::post('/admin/settings/recordings', [AdminToolsController::class, 'updateRecordingSettings'])->name('settings.recordings.update');
    Route::patch('/admin/settings/recording-users/{user}', [AdminToolsController::class, 'updateRecordingPermission'])->name('settings.recording-users.update');
    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::get('/meetings/{meeting}/details', [MeetingController::class, 'details'])->name('meetings.details');
    Route::get('/meetings/{meeting}/lobby', [MeetingController::class, 'lobby'])->name('meetings.lobby');
    Route::post('/meetings/{meeting}/companies', [MeetingController::class, 'addCompany'])->name('meetings.companies.store');
    Route::patch('/meetings/{meeting}/companies/{company}', [MeetingController::class, 'updateCompany'])->name('meetings.companies.update');
    Route::delete('/meetings/{meeting}/companies/{company}', [MeetingController::class, 'removeCompany'])->name('meetings.companies.destroy');
    Route::post('/meetings/{meeting}/companies/{company}/invite', [MeetingController::class, 'inviteCompany'])->name('meetings.companies.invite');
    Route::post('/meetings/{meeting}/companies/{company}/remind', [MeetingController::class, 'remindCompany'])->name('meetings.companies.remind');
    Route::post('/meetings/{meeting}/companies/{company}/invitation', [MeetingController::class, 'issueCompanyInvitation'])->name('meetings.companies.invitation.issue');
    Route::post('/meetings/{meeting}/companies/{company}/invitation/resend', [MeetingController::class, 'resendCompanyInvitation'])->name('meetings.companies.invitation.resend');
    Route::delete('/meetings/{meeting}/companies/{company}/invitation', [MeetingController::class, 'revokeCompanyInvitation'])->name('meetings.companies.invitation.revoke');
    Route::post('/participant-approvals/{approval}/approve', [MeetingController::class, 'approveParticipantApproval'])->name('approvals.approve');
    Route::post('/participant-approvals/{approval}/reject', [MeetingController::class, 'rejectParticipantApproval'])->name('approvals.reject');
    Route::get('/companies/search', [MeetingController::class, 'searchCompanies'])->name('meetings.companies.search');
    Route::post('/meetings/{meeting}/invitations', [MeetingController::class, 'sendInvitations'])->name('meetings.invitations.send');
    Route::post('/meetings/{meeting}/invitations/remind', [MeetingController::class, 'remindInvitations'])->name('meetings.invitations.remind');
    Route::post('/meetings/{meeting}/settings', [MeetingController::class, 'updateSettings'])->name('meetings.settings.update');
    Route::get('/admin/meetings/live', [MeetingController::class, 'liveMeetings'])->name('meetings.live');
    Route::get('/admin/meetings/check-ins', [MeetingController::class, 'checkIns'])->name('meetings.check-ins');
    Route::get('/admin/meetings/all', [MeetingController::class, 'allMeetings'])->name('meetings.all');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::post('/meeting-rooms', [MeetingController::class, 'storeRoom'])->name('meeting-rooms.store');
    Route::get('/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
    Route::get('/meetings/create/hybrid', [MeetingController::class, 'createHybrid'])->name('meetings.create.hybrid');
    Route::post('/meetings/create/hybrid/step1', [MeetingController::class, 'createHybridStep1'])->name('meetings.create.hybrid.step1');
    Route::get('/meetings/create/hybrid/step2', [MeetingController::class, 'createHybridStep2'])->name('meetings.create.hybrid.step2');
    Route::post('/meetings/create/hybrid/step2', [MeetingController::class, 'createHybridStep2Store'])->name('meetings.create.hybrid.step2.store');
    Route::get('/meetings/create/hybrid/step3', [MeetingController::class, 'createHybridStep3'])->name('meetings.create.hybrid.step3');
    Route::post('/meetings/create/hybrid/step3', [MeetingController::class, 'createHybridStep3Store'])->name('meetings.create.hybrid.step3.store');
    Route::post('/meetings/create/hybrid/back', [MeetingController::class, 'createHybridBack'])->name('meetings.create.hybrid.back');

    Route::get('/meetings/{meeting}/reschedule', [MeetingController::class, 'editReschedule'])->name('meetings.reschedule.edit');
    Route::get('/meetings/{meeting}/attendance', [MeetingController::class, 'attendance'])->name('meetings.attendance');
    Route::get('/meetings/{meeting}/edit', [MeetingController::class, 'edit'])->name('meetings.edit');
    Route::get('/meetings/{meeting}/badges', [MeetingController::class, 'badges'])->name('meetings.badges');
    Route::get('/meetings/{meeting}/pdf', [MeetingController::class, 'exportPdf'])->name('meetings.pdf');
    Route::patch('/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
    Route::post('/meetings/{meeting}/cancel', [MeetingController::class, 'cancel'])->name('meetings.cancel');
    Route::post('/meetings/{meeting}/reopen', [MeetingController::class, 'reopen'])->name('meetings.reopen');
    Route::post('/meetings/{meeting}/lock', [MeetingController::class, 'lock'])->name('meetings.lock');
    Route::post('/meetings/{meeting}/unlock', [MeetingController::class, 'unlock'])->name('meetings.unlock');
    Route::post('/meetings/{meeting}/notes', [MeetingController::class, 'saveNotes'])->name('meetings.notes');
    Route::patch('/meetings/{meeting}/reschedule', [MeetingController::class, 'reschedule'])->name('meetings.reschedule');
    Route::post('/meetings/{meeting}/attendees', [MeetingController::class, 'addAttendee'])->name('attendees.store');
    Route::post('/meetings/{meeting}/attendees/import', [MeetingController::class, 'importAttendees'])->name('attendees.import');
    Route::get('/attendees/template', [MeetingController::class, 'downloadTemplate'])->name('attendees.template');
    Route::post('/meetings/{meeting}/check-in', [MeetingController::class, 'checkIn'])->name('attendees.check-in');
    Route::post('/attendees/{attendee}/undo-check-in', [MeetingController::class, 'undoCheckIn'])->name('attendees.undo-check-in');

    // Multi-Company Meeting Routes
    Route::get('/multi-company-meetings/create', [MultiCompanyMeetingController::class, 'create'])->name('multi-company-meetings.create');
    Route::post('/multi-company-meetings/store', [MultiCompanyMeetingController::class, 'store'])->name('multi-company-meetings.store');
    Route::post('/multi-company-meetings/import-companies', [MultiCompanyMeetingController::class, 'importCompanies'])->name('multi-company-meetings.import-companies');
    Route::get('/multi-company-meetings/{meeting}', [MultiCompanyMeetingController::class, 'show'])->name('multi-company-meetings.show');
    Route::post('/multi-company-meetings/{meeting}/companies/{company}/invitation/resend', [MultiCompanyMeetingController::class, 'resendInvitation'])->name('multi-company-meetings.invitation.resend');
    Route::delete('/multi-company-meetings/{meeting}/companies/{company}/invitation', [MultiCompanyMeetingController::class, 'revokeInvitation'])->name('multi-company-meetings.invitation.revoke');

    // Admin Live Session Monitor
    Route::get('/admin/live-monitor', [AdminToolsController::class, 'liveSessionMonitor'])->name('admin.live-monitor');
    Route::get('/admin/live-monitor/api', [AdminToolsController::class, 'liveSessionMonitorApi'])->name('admin.live-monitor.api');

    // Platform Company Management
    Route::prefix('admin/companies')->name('admin.companies.')->group(function () {
        Route::get('/', [AdminToolsController::class, 'companies'])->name('index');
        Route::get('create', [AdminToolsController::class, 'createCompany'])->name('create');
        Route::post('/', [AdminToolsController::class, 'storeCompany'])->name('store');
        Route::get('{company}', [AdminToolsController::class, 'showCompany'])->name('show');
        Route::get('{company}/edit', [AdminToolsController::class, 'editCompany'])->name('edit');
        Route::patch('{company}', [AdminToolsController::class, 'updateCompany'])->name('update');
        Route::delete('{company}', [AdminToolsController::class, 'destroyCompany'])->name('destroy');

        // Company Import Routes
        Route::prefix('import')->name('import.')->group(function () {
            Route::get('template', [CompanyImportController::class, 'downloadTemplate'])->name('template');
            Route::get('imports', [CompanyImportController::class, 'index'])->name('imports');
            Route::get('create', [CompanyImportController::class, 'create'])->name('create');
            Route::post('preview', [CompanyImportController::class, 'preview'])->name('preview');
            Route::post('/', [CompanyImportController::class, 'import'])->name('store');
            Route::get('imports/{import}', [CompanyImportController::class, 'show'])->name('show');
        });
    });
    Route::post('/multi-company-meetings/{meeting}/cancel', [MultiCompanyMeetingController::class, 'cancelMeeting'])->name('multi-company-meetings.cancel');
    Route::get('/multi-company-meetings/{meeting}/export-attendance', [MultiCompanyMeetingController::class, 'exportAttendance'])->name('multi-company-meetings.attendance.export');
    Route::post('/attendees/{attendee}/check-out', [MeetingController::class, 'toggleCheckOut'])->name('attendees.check-out');
    Route::post('/attendees/{attendee}', [MeetingController::class, 'destroyAttendee'])->name('attendees.destroy');
    Route::post('/attendees/{attendee}/approve', [MeetingController::class, 'approveAttendee'])->name('attendees.approve');
    Route::post('/attendees/{attendee}/reject', [MeetingController::class, 'rejectAttendee'])->name('attendees.reject');

    // Recurring Meeting Series Routes
    Route::get('/meetings/{meeting}/series/edit', [MeetingController::class, 'editSeriesOccurrence'])->name('meetings.series.edit');
    Route::patch('/meetings/{meeting}/series/update', [MeetingController::class, 'updateSeriesOccurrence'])->name('meetings.series.update');
    Route::post('/meeting-exceptions/{exception}/approve', [MeetingController::class, 'approveMeetingException'])->name('meetings.exceptions.approve');
    Route::post('/meeting-exceptions/{exception}/reject', [MeetingController::class, 'rejectMeetingException'])->name('meetings.exceptions.reject');

    // Online sessions
    Route::get('/online-sessions', [OnlineSessionController::class, 'index'])->name('sessions.index');
    Route::get('/online-sessions/quick-start', [OnlineSessionController::class, 'quickStart'])->name('sessions.quick-start');
    Route::post('/meetings/{meeting}/online-session', [OnlineSessionController::class, 'createSession'])->name('sessions.create');
    Route::delete('/meetings/{meeting}/online-session', [OnlineSessionController::class, 'destroySession'])->name('sessions.destroy');
    Route::delete('/meetings/{meeting}/session-recordings/{recording}', [OnlineSessionController::class, 'deleteRecording'])->name('sessions.recordings.delete');

});
