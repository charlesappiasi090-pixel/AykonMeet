<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MeetingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::get('/join', function () {
    return redirect()->route('meetings.show', request()->query('code', ''));
})->name('meetings.join');

Route::get('/meetings/{code}', [MeetingController::class, 'show'])->name('meetings.show');

Route::get('/kiosk/{code}', [MeetingController::class, 'kiosk'])->name('kiosk.show');
Route::post('/meetings/{meeting}/kiosk-check-in', [MeetingController::class, 'kioskCheckIn'])->name('kiosk.check-in');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/admin', [MeetingController::class, 'index'])->name('admin.dashboard');
    Route::get('/meetings', [MeetingController::class, 'index'])->name('meetings.index');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::post('/meeting-rooms', [MeetingController::class, 'storeRoom'])->name('meeting-rooms.store');
    Route::get('/meetings/{meeting}/reschedule', [MeetingController::class, 'editReschedule'])->name('meetings.reschedule.edit');
    Route::get('/meetings/{meeting}/attendance', [MeetingController::class, 'attendance'])->name('meetings.attendance');
    Route::get('/meetings/{meeting}/edit', [MeetingController::class, 'edit'])->name('meetings.edit');
    Route::get('/meetings/{meeting}/badges', [MeetingController::class, 'badges'])->name('meetings.badges');
    Route::get('/meetings/{meeting}/pdf', [MeetingController::class, 'exportPdf'])->name('meetings.pdf');
    Route::patch('/meetings/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
    Route::post('/meetings/{meeting}/start', [MeetingController::class, 'start'])->name('meetings.start');
    Route::post('/meetings/{meeting}/end', [MeetingController::class, 'end'])->name('meetings.end');
    Route::post('/meetings/{meeting}/cancel', [MeetingController::class, 'cancel'])->name('meetings.cancel');
    Route::post('/meetings/{meeting}/reopen', [MeetingController::class, 'reopen'])->name('meetings.reopen');
    Route::post('/meetings/{meeting}/notes', [MeetingController::class, 'saveNotes'])->name('meetings.notes');
    Route::patch('/meetings/{meeting}/reschedule', [MeetingController::class, 'reschedule'])->name('meetings.reschedule');
    Route::post('/meetings/{meeting}/attendees', [MeetingController::class, 'addAttendee'])->name('attendees.store');
    Route::post('/meetings/{meeting}/attendees/import', [MeetingController::class, 'importAttendees'])->name('attendees.import');
    Route::get('/attendees/template', [MeetingController::class, 'downloadTemplate'])->name('attendees.template');
    Route::post('/meetings/{meeting}/check-in', [MeetingController::class, 'checkIn'])->name('attendees.check-in');
    Route::post('/attendees/{attendee}/undo-check-in', [MeetingController::class, 'undoCheckIn'])->name('attendees.undo-check-in');
    Route::post('/attendees/{attendee}/check-out', [MeetingController::class, 'toggleCheckOut'])->name('attendees.check-out');
    Route::post('/attendees/{attendee}', [MeetingController::class, 'destroyAttendee'])->name('attendees.destroy');
});
