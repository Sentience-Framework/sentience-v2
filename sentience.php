<?php

use src\dotenv\DotEnv;
use src\sentience\Sentience;

require_once __DIR__ . '/vendor/autoload.php';

define('SENTIENCE_DIR', __DIR__);

DotEnv::loadEnv(false, false);

DotEnv::loadFile(
    path(SENTIENCE_DIR, '.env'),
    path(SENTIENCE_DIR, '.env.example'),
    [
        'SENTIENCE_DIR' => SENTIENCE_DIR,
        ...$_ENV
    ]
);

$sentience = new Sentience();

require_once path(SENTIENCE_DIR, 'commands.php');
require_once path(SENTIENCE_DIR, 'routes.php');

$sentience->execute();
