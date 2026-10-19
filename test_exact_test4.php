<?php

use App\Http\Controllers\OnlineSessionController;
use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\MeetingCompany;
use App\Models\OnlineSessionEmailVerification;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

// Exact copy of the test's createMeeting method
function createMeeting(array $attributes = []): Meeting
{
    return Meeting::create(array_merge([
        'meeting_code' => 'AYK-SEC'.random_int(1000, 9999),
        'name' => 'Meeting host',
        'email' => 'host@example.test',
        'phone' => '555-0100',
        'meeting_topic' => 'Host permissions session',
        'start_date' => now(),
        'end_date' => now()->addHour(),
        'status' => 'in_progress',
        'is_online' => true,
    ], $attributes));
}

// Exact copy of the test's createAttendee method
function createAttendee(Meeting $meeting, array $attributes = []): Attendee
{
    MeetingCompany::forName($meeting, 'Aykon HQ', true);
    $company = MeetingCompany::forName($meeting, 'Acme Ltd');

    return Attendee::create(array_merge([
        'meeting_id' => $meeting->id,
        'meeting_company_id' => $company->id,
        'department' => 'Operations',
        'full_name' => 'Ravi Patel',
        'email' => 'ravi@acme.test',
        'phone' => '5550102',
        'invitation_status' => 'pending',
    ], $attributes));
}

// Exact copy of the test's requestEmailVerificationCode method
function requestEmailVerificationCode(Meeting $meeting, Attendee $attendee): string
{
    $request = Request::create(
        URL::signedRoute('sessions.participants.send-code', ['meeting' => $meeting]),
        'POST',
        ['email' => $attendee->email]
    );

    $controller = app()->make(OnlineSessionController::class);
    $response = app()->make(OnlineSessionController::class)->sendParticipantVerificationCode($request, $meeting);

    if ($response->getStatusCode() !== 200) {
        throw new Exception('Failed to send code: '.$response->getContent());
    }

    // Get the code from the verification record
    $verification = OnlineSessionEmailVerification::where('meeting_id', $meeting->id)
        ->where('attendee_id', $attendee->id)
        ->latest()
        ->first();

    if (! $verification) {
        throw new Exception('No verification record found');
    }

    // We can't get the actual code from the hash, so we'll set a known code
    $knownCode = '123456';
    $verification->update([
        'code_hash' => hash_hmac('sha256', $knownCode, (string) config('app.key')),
        'expires_at' => now()->addMinutes(10),
        'attempts' => 0,
    ]);

    return $knownCode;
}

// Create meeting exactly like the test
$scheduledMeeting = createMeeting([
    'status' => 'scheduled',
    'start_date' => now()->addHour(),
    'settings' => ['waiting_room' => true],
]);

echo 'Meeting created: ID='.$scheduledMeeting->id.', status='.$scheduledMeeting->status.PHP_EOL;

// Create attendee exactly like the test
$attendee = createAttendee($scheduledMeeting);
echo 'Attendee email: '.$attendee->email.PHP_EOL;

// Request verification code exactly like the test
$code = requestEmailVerificationCode($scheduledMeeting, $attendee);
echo "Verification code: $code".PHP_EOL;

// Now join exactly like the test
$joinUrl = URL::signedRoute('sessions.participants.join', ['meeting' => $scheduledMeeting]);
$joinRequest = Request::create($joinUrl, 'POST', [
    'email' => $attendee->email,
    'verification_code' => $code,
]);

$controller = app()->make(OnlineSessionController::class);

try {
    $joinResponse = $controller->joinParticipant($joinRequest, $scheduledMeeting);
    echo 'Join response status: '.$joinResponse->getStatusCode().PHP_EOL;
    echo 'Join response body: '.$joinResponse->getContent().PHP_EOL;
} catch (Throwable $e) {
    echo 'Error joining: '.$e->getMessage().PHP_EOL;
    echo $e->getTraceAsString().PHP_EOL;
}
