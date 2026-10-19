<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo 'Bootstrapped'.PHP_EOL;

try {
    $routes = app('router')->getRoutes();
    echo 'Total routes: '.$routes->count().PHP_EOL;

    foreach ($routes as $route) {
        $action = $route->getActionName();
        if (strpos($action, 'Company') !== false) {
            echo 'Route: '.$route->uri().' -> '.$action.PHP_EOL;
        }
    }
} catch (Throwable $e) {
    echo 'Error: '.$e->getMessage().PHP_EOL;
    echo $e->getTraceAsString().PHP_EOL;
}
