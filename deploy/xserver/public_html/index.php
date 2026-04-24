<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Change this to the absolute path where Laravel is placed on Xserver.
$laravelBasePath = '/home/your_account/laravel_app';

if (file_exists($maintenance = $laravelBasePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

$autoloadPath = $laravelBasePath.'/vendor/autoload.php';

if (! is_file($autoloadPath)) {
    error_log('Laravel autoload file is missing: '.$autoloadPath);
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Service temporarily unavailable.';
    exit;
}

require $autoloadPath;

/** @var Application $app */
$app = require_once $laravelBasePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
