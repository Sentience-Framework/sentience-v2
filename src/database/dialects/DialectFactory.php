<?php

namespace src\database\dialects;

class DialectFactory
{
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
