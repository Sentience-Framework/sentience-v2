<?php

use src\controllers\DevToolsController;
use src\controllers\ExampleController;
use src\controllers\SentienceController;
use src\routers\Command;
use src\sentience\Sentience;

/**
 * @var Sentience $sentience
 */

$commands = [
    Command::create('server:start')
        ->setCallback([SentienceController::class, 'startServer']),

    Command::create('migrations:init')
        ->setCallback([SentienceController::class, 'initMigrations']),

    Command::create('migrations:apply')
        ->setCallback([SentienceController::class, 'applyMigrations']),

    Command::create('migrations:rollback')
        ->setCallback([SentienceController::class, 'rollbackMigrations']),

    Command::create('migrations:create')
        ->setCallback([SentienceController::class, 'createMigration']),

    Command::create('models:init')
        ->setCallback([SentienceController::class, 'initModel']),

    Command::create('models:reset')
        ->setCallback([SentienceController::class, 'resetModel']),

    Command::create('dotenv:fix')
        ->setCallback([SentienceController::class, 'fixDotEnv']),

    Command::create('dev-tools:sort-imports')
        ->setCallback([DevToolsController::class, 'sortImports']),

    Command::create('dev-tools:remove-trailing-commas')
        ->setCallback([DevToolsController::class, 'removeTrailingCommas']),

    Command::create('example')
        ->setCallback([ExampleController::class, 'cliExample']),

    Command::create('query')
        ->setCallback([ExampleController::class, 'query'])
];

foreach ($commands as $command) {
    $sentience->bindCommand($command);
}
