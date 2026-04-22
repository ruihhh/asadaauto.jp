<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Change this to the absolute path where Laravel is placed on Xserver.
$laravelBasePath = '/home/your_account/laravel_app';

if (file_exists($maintenance = $laravelBasePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $laravelBasePath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $laravelBasePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
