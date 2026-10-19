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

// Simulate the test's createMeeting method exactly
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

// Create meeting exactly like the test
$scheduledMeeting = createMeeting([
    'status' => 'scheduled',
    'start_date' => now()->addHour(),
    'settings' => ['waiting_room' => true],
]);

echo 'Meeting created:'.PHP_EOL;
echo '  ID: '.$scheduledMeeting->id.PHP_EOL;
echo '  Status: '.$scheduledMeeting->status.PHP_EOL;
echo '  Start date: '.$scheduledMeeting->start_date.PHP_EOL;
echo '  End date: '.$scheduledMeeting->end_date.PHP_EOL;
echo '  Started at: '.($scheduledMeeting->started_at ? $scheduledMeeting->started_at : 'null').PHP_EOL;
echo '  Ended at: '.($scheduledMeeting->ended_at ? $scheduledMeeting->ended_at : 'null').PHP_EOL;
echo '  Is online: '.($scheduledMeeting->is_online ? 'true' : 'false').PHP_EOL;
echo '  Join state: '.$scheduledMeeting->inviteeJoinState().PHP_EOL;
echo 'Waiting room setting: '.($scheduledMeeting->setting('waiting_room') ? 'true' : 'false').PHP_EOL;
echo 'Settings: '.json_encode($scheduledMeeting->settings).PHP_EOL;

// Create attendee
$company = MeetingCompany::forName($scheduledMeeting, 'Test Company');
$attendee = Attendee::create([
    'meeting_id' => $scheduledMeeting->id,
    'meeting_company_id' => $company->id,
    'full_name' => 'Test Attendee',
    'email' => 'attendee@test.com',
    'phone' => '555-0100',
    'invitation_status' => 'accepted',
]);

echo 'Attendee email: '.$attendee->email.PHP_EOL;

// Request verification code
$request = Request::create(
    URL::signedRoute('sessions.participants.send-code', ['meeting' => $scheduledMeeting]),
    'POST',
    ['email' => $attendee->email]
);

$controller = app()->make(OnlineSessionController::class);

$response = $controller->sendParticipantVerificationCode($request, $scheduledMeeting);
echo 'Send code response: '.$response->getStatusCode().PHP_EOL;

// Get the verification code
$verification = OnlineSessionEmailVerification::where('meeting_id', $scheduledMeeting->id)
    ->where('attendee_id', $attendee->id)
    ->latest()
    ->first();

if ($verification) {
    // Manually set a known code
    $knownCode = '123456';
    $verification->update([
        'code_hash' => hash_hmac('sha256', $knownCode, (string) config('app.key')),
        'expires_at' => now()->addMinutes(10),
        'attempts' => 0,
    ]);
    echo "Using known code: $knownCode".PHP_EOL;
} else {
    echo 'No verification record found'.PHP_EOL;
    exit;
}

// Now test the join flow with the known code
$joinRequest = Request::create(
    URL::signedRoute('sessions.participants.join', ['meeting' => $scheduledMeeting]),
    'POST',
    [
        'email' => $attendee->email,
        'verification_code' => '123456',
    ]
);

$controller = app()->make(OnlineSessionController::class);

try {
    $joinResponse = $controller->joinParticipant($joinRequest, $scheduledMeeting);
    echo 'Join response status: '.$joinResponse->getStatusCode().PHP_EOL;
    echo 'Join response body: '.$joinResponse->getContent().PHP_EOL;
} catch (Throwable $e) {
    echo 'Error joining: '.$e->getMessage().PHP_EOL;
    echo $e->getTraceAsString().PHP_EOL;
}
