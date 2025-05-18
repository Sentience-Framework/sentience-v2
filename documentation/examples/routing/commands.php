<?php

use src\controllers\SentienceController;
use src\routers\Command;
use src\sentience\Stdio;

[
    Command::create('migrations:init')
        ->setCallback([SentienceController::class, 'initMigrations'])
        ->setMiddleware([
            function (array $words): void {
                if (count($words) == 0) {
                    Stdio::errorLn('no $words provided');
                }
            }
        ]),

    Command::create('migrations:apply')
        ->setCallback([SentienceController::class, 'applyMigrations'])
        ->setMiddleware([
            function (array $flags): void {
                if (count($flags) == 0) {
                    Stdio::errorLn('no $flags provided');
                }
            }
        ])
];
