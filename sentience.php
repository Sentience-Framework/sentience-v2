<?php

use src\dotenv\DotEnv;
use src\sentience\Sentience;

require_once 'vendor/autoload.php';

define('SENTIENCE_DIR', __DIR__);

DotEnv::loadEnv(false, false);

DotEnv::loadFile(
    file_path(SENTIENCE_DIR, '.env'),
    file_path(SENTIENCE_DIR, '.env.example'),
    [
        'SENTIENCE_DIR' => SENTIENCE_DIR,
        ...$_ENV
    ]
);

$sentience = new Sentience();

require_once 'commands.php';
require_once 'routes.php';

$sentience->execute();
