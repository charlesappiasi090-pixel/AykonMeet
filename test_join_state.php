<?php

use App\Models\Meeting;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$m = Meeting::create([
    'meeting_code' => 'TEST123',
    'name' => 'Test',
    'email' => 'test@test.com',
    'phone' => '123456',
    'meeting_topic' => 'Test',
    'start_date' => now()->addHour(),
    'end_date' => now()->addHours(2),
    'status' => 'scheduled',
    'is_online' => true,
    'is_multi_company' => true,
    'settings' => ['waiting_room' => true],
]);

echo 'Status: '.$m->status.PHP_EOL;
echo 'Started at: '.($m->started_at ? $m->started_at : 'null').PHP_EOL;
echo 'Ended at: '.($m->ended_at ? $m->ended_at : 'null').PHP_EOL;
echo 'Join state: '.$m->inviteeJoinState().PHP_EOL;
echo 'Waiting room setting: '.($m->setting('waiting_room') ? 'true' : 'false').PHP_EOL;
