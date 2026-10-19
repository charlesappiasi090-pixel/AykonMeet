<?php

use App\Models\Meeting;
use Illuminate\Contracts\Console\Kernel;

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

// Check ensureJoinable
try {
    abort_unless($scheduledMeeting->is_online, 404);
    abort_if(in_array($scheduledMeeting->status, ['cancelled', 'completed'], true), 410);
    echo 'ensureJoinable: PASS'.PHP_EOL;
} catch (Throwable $e) {
    echo 'ensureJoinable: FAIL - '.$e->getMessage().PHP_EOL;
}

// Check inviteeJoinState
$joinState = $scheduledMeeting->inviteeJoinState();
echo "inviteeJoinState: $joinState".PHP_EOL;

if ($joinState === 'closed') {
    echo 'Would return 410'.PHP_EOL;
} else {
    echo "Would proceed (joinState: $joinState)".PHP_EOL;
}
