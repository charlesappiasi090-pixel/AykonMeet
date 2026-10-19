<?php

$code = file_get_contents('app/Http/Controllers/MultiCompanyMeetingController.php');
$bytes = [];
for ($i = 17150; $i < 17180; $i++) {
    $bytes[] = sprintf('%02X', ord($code[$i]));
}
echo 'Bytes 17150-17179: '.implode(' ', $bytes)."\n";

$lines = explode("\n", $code);
for ($i = 283; $i <= 288; $i++) {
    $line = $lines[$i];
    $hex = bin2hex($line);
    echo ($i + 1).': ['.$hex.'] '.json_encode($line)."\n";
}
