<?php

use src\database\Database;
use src\exceptions\DatabaseException;
use src\sentience\Stdio;
use src\utils\Terminal;

/**
 * An anonymous class to define your global variables
 * These variables will be injected as arguments in callbacks
 *
 * When a method or function calls for $database, the public function database()
 * Will be executed
 *
 * Only the variables that are listed as arguments will be injected
 * A good use case would be putting the database connection variable here
 */

return new class {
    public function database(): Database
    {
        $debugCallback = env('DB_DEBUG', false)
            ? function (string $query, ?float $startTime, ?float $endTime, ?string $error): void {
                $terminalWidth = Terminal::getWidth();

                $equalSigns = ($terminalWidth - 5) / 2 - 1;

                Stdio::errorFLn(
                    '%s Query %s',
                    str_repeat('=', ceil($equalSigns)),
                    str_repeat('=', floor($equalSigns))
                );

                Stdio::errorFLn('Timestamp : %s', date('Y-m-d H:i:s'));
                Stdio::errorFLn('Query     : %s', $query);

                if ($startTime && $endTime) {
                    Stdio::errorFLn('Time      : %s ms', round(($endTime - $startTime) * 1000, 2));
                }

                if ($error) {
                    Stdio::errorFLn('Error     : %s', $error);
                }

                Stdio::errorLn(str_repeat('=', $terminalWidth));
            }
            : null;

        $dsn = env('DB_DSN');

        if (!$dsn) {
            throw new DatabaseException('no DB_DSN defined in environment', $dsn);
        }

        return new Database($dsn, $debugCallback);
    }
};
