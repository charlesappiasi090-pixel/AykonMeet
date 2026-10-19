<?php

require 'vendor/autoload.php';
$code = file_get_contents('app/Http/Controllers/MultiCompanyMeetingController.php');
eval('?>'.$code);
echo "OK\n";
