<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
echo 'Bootstrapped'.PHP_EOL;
$c = new ReflectionClass('App\Http\Controllers\CompanyImportController');
echo 'File: '.$c->getFileName().PHP_EOL;
echo 'Class exists: '.($c->isInstantiable() ? 'yes' : 'no').PHP_EOL;
echo 'Methods: '.count($c->getMethods()).PHP_EOL;
