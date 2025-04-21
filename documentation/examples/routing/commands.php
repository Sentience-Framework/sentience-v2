<?php

[
    Command::create('migrations:init')
        ->setCallback([MigrationsController::class, 'initMigrations'])
        ->setMiddleware([
            [AdminMiddleware::class, 'isAuthenticated']
        ]),

    Command::create('migrations:apply')
        ->setCallback([MigrationsController::class, 'applyMigrations'])
        ->setMiddleware([
            [AdminMiddleware::class, 'isAuthenticated']
        ]),
];
