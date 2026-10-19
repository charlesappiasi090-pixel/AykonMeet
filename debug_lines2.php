<?php

$code = file_get_contents('app/Http/Controllers/MultiCompanyMeetingController.php');
$lines = explode("\n", $code);
for ($i = 279; $i <= 295; $i++) {
    echo ($i + 1).': '.json_encode($lines[$i]).PHP_EOL;
}
