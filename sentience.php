<?php

use src\dotenv\DotEnv;
use src\sentience\Sentience;
use src\utils\Filesystem;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/ini.php';

define('SENTIENCE_DIR', __DIR__);

DotEnv::loadEnv();
DotEnv::loadFile(
    Filesystem::path(SENTIENCE_DIR, '.env'),
    Filesystem::path(SENTIENCE_DIR, '.env.example'),
    [
        'SENTIENCE_DIR' => SENTIENCE_DIR,
        ...$_ENV
    ]
);

$commands = require Filesystem::path(SENTIENCE_DIR, 'commands.php');
$routes = require Filesystem::path(SENTIENCE_DIR, 'routes.php');
$service = require Filesystem::path(SENTIENCE_DIR, 'service.php');

$sentience = new Sentience($service);

foreach ($commands as $command) {
    $sentience->bindCommand($command);
}

foreach ($routes as $route) {
    $sentience->bindRoute($route);
}

$sentience->execute();
