<?php

use App\Http\Controllers\OnlineSessionController;
use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\MeetingCompany;
use App\Models\OnlineSessionEmailVerification;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

// Create a test user
$user = User::factory()->create();

// Create a meeting like the test does
$attributes = [
    'status' => 'scheduled',
    'start_date' => now()->addHour(),
    'settings' => ['waiting_room' => true],
];

$merged = array_merge([
    'meeting_code' => 'AYK-SEC'.random_int(1000, 9999),
    'name' => 'Test Host',
    'email' => 'host@example.test',
    'phone' => '555-0100',
    'meeting_topic' => 'Secure Meeting',
    'start_date' => now()->addHour(),
    'end_date' => now()->addHours(2),
    'status' => 'in_progress',
    'is_online' => true,
    'is_multi_company' => true,
    'settings' => ['waiting_room' => false],
], $attributes);

$meeting = Meeting::create($merged);

// Create an attendee
$company = MeetingCompany::forName($meeting, 'Test Company');
$attendee = Attendee::create([
    'meeting_id' => $meeting->id,
    'meeting_company_id' => $company->id,
    'full_name' => 'Test Attendee',
    'email' => 'attendee'.random_int(1000, 9999).'@example.test',
    'phone' => '555-0100',
    'invitation_status' => 'accepted',
]);

// Request verification code
$request = Request::create(
    URL::signedRoute('sessions.participants.send-code', ['meeting' => $meeting]),
    'POST',
    ['email' => $attendee->email]
);
$request->setUserResolver(fn () => $user);

// Call the controller method
$controller = app()->make(OnlineSessionController::class);

$response = $controller->sendParticipantVerificationCode($request, $meeting);
echo 'Send code response: '.$response->getStatusCode().PHP_EOL;

// Get the verification code from the database
$verification = OnlineSessionEmailVerification::where('meeting_id', $meeting->id)
    ->where('attendee_id', $attendee->id)
    ->latest()
    ->first();

if ($verification) {
    // We can't easily get the code from the hash, so let's just use a known code
    // The code is generated as a 6-digit string
    // For testing, let's just try with a dummy code and see what happens
    echo 'Verification record created'.PHP_EOL;
} else {
    echo 'No verification record found'.PHP_EOL;
}

// Now test the join flow with a dummy code
$joinRequest = Request::create(
    URL::signedRoute('sessions.participants.join', ['meeting' => $meeting]),
    'POST',
    [
        'email' => $attendee->email,
        'verification_code' => '123456',
    ]
);
$joinRequest->setUserResolver(fn () => $user);

$controller = app()->make(OnlineSessionController::class);

try {
    $joinResponse = $controller->joinParticipant($joinRequest, $meeting);
    echo 'Join response status: '.$joinResponse->getStatusCode().PHP_EOL;
    echo 'Join response body: '.$joinResponse->getContent().PHP_EOL;
} catch (Throwable $e) {
    echo 'Error joining: '.$e->getMessage().PHP_EOL;
    echo $e->getTraceAsString().PHP_EOL;
}
