<?php

$commands = [
    Command::register(
        'migrations:init',
        [SentienceController::class, 'initMigrations']
    )->setMiddleware([
                [AdminMiddleware::class, 'isAuthenticated']
            ]),

    Command::register(
        'migrations:apply',
        [SentienceController::class, 'applyMigrations']
    )->setMiddleware([
                [AdminMiddleware::class, 'isAuthenticated']
            ]),

    Command::register(
        'migrations:rollback',
        [SentienceController::class, 'rollbackMigrations']
    )->setMiddleware([
                [AdminMiddleware::class, 'isAuthenticated']
            ])
];
