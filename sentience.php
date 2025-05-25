<?php

use src\dotenv\DotEnv;
use src\sentience\Sentience;

require_once __DIR__ . '/vendor/autoload.php';

define('SENTIENCE_DIR', __DIR__);

DotEnv::loadEnv();
DotEnv::loadFile(
    __DIR__ . '/.env',
    __DIR__ . '/.env.example',
    [
        'SENTIENCE_DIR' => SENTIENCE_DIR,
        ...$_ENV
    ]
);

$commands = require __DIR__ . '/commands.php';
$routes = require __DIR__ . '/routes.php';
$service = require __DIR__ . '/service.php';

$sentience = new Sentience($service);

foreach ($commands as $command) {
    $sentience->bindCommand($command);
}

foreach ($routes as $route) {
    $sentience->bindRoute($route);
}

$sentience->execute();
