<?php

require 'vendor/autoload.php';
$c = new ReflectionClass('App\Http\Controllers\CompanyImportController');
echo 'Class exists: '.$c->getName();
