<?php

require 'vendor/autoload.php';
$c = new ReflectionClass('App\Http\Controllers\CompanyImportController');
echo 'File: '.$c->getFileName()."\n";
echo 'Class exists: '.($c->isInstantiable() ? 'yes' : 'no')."\n";
echo 'Methods: '.count($c->getMethods())."\n";
