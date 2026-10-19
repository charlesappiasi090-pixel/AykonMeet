<?php

use App\Http\Controllers\OnlineSessionController;
use App\Models\Attendee;
use App\Models\Meeting;
use App\Models\MeetingCompany;
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
    'email' => 'attendee'.random_int(1000, 9999).'@example.test',
    'phone' => '555-0100',
    'invitation_status' => 'accepted',
]);

// Now simulate the join request
$request = Request::create(
    URL::signedRoute('sessions.participants.join', ['meeting' => $scheduledMeeting]),
    'POST',
    [
        'email' => 'attendee@test.com',  // Wrong email
        'verification_code' => '123456',
    ]
);

$controller = app()->make(OnlineSessionController::class);

try {
    $response = $controller->joinParticipant($request, $scheduledMeeting);
    echo 'Response status: '.$response->getStatusCode().PHP_EOL;
    echo 'Response body: '.$response->getContent().PHP_EOL;
} catch (Throwable $e) {
    echo 'Error: '.$e->getMessage().PHP_EOL;
}
