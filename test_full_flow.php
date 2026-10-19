<?php

use App\Http\Controllers\OnlineSessionController;
use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\MeetingCompany;
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

try {
    $response = $controller->sendParticipantVerificationCode($request, $meeting);
    echo 'Send code response: '.$response->getStatusCode().PHP_EOL;
    echo 'Send code response body: '.$response->getContent().PHP_EOL;
} catch (Throwable $e) {
    echo 'Error sending code: '.$e->getMessage().PHP_EOL;
    echo $e->getTraceAsString().PHP_EOL;
}
