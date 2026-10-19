<?php

use App\Models\Meeting;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

// Simulate the test createMeeting method
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

$m = Meeting::create($merged);

echo 'Status: '.$m->status.PHP_EOL;
echo 'Started at: '.($m->started_at ? $m->started_at : 'null').PHP_EOL;
echo 'Ended at: '.($m->ended_at ? $m->ended_at : 'null').PHP_EOL;
echo 'Join state: '.$m->inviteeJoinState().PHP_EOL;
echo 'Waiting room setting: '.($m->setting('waiting_room') ? 'true' : 'false').PHP_EOL;
echo 'Settings: '.json_encode($m->settings).PHP_EOL;
