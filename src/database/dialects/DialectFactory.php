<?php

namespace src\database\dialects;

use PDO;
use src\database\Database;

class DialectFactory
{
    public static function fromDatabase(Database $database): DialectInterface
    {
        return static::fromDriver($database->getPDOAttribute(PDO::ATTR_DRIVER_NAME));
    }

    public static function fromDriver(string $driver): DialectInterface
    {
        return match ($driver) {
            'mysql' => new Mysql(),
            'pgsql' => new Pgsql(),
            'sqlite' => new Sqlite(),
            default => new Sql()
        };
    }
}
