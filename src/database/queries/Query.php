<?php

namespace src\database\queries;

use DateTime;
use src\database\Database;
use src\database\dialects\DialectInterface;
use src\database\queries\containers\Alias;
use src\database\queries\containers\Raw;
use src\database\Results;

abstract class Query implements QueryInterface
{
    protected Database $database;
    protected DialectInterface $dialect;
    protected string $query;
    protected array $params;

    public function __construct(Database $database, DialectInterface $dialect)
    {
        $this->database = $database;
        $this->dialect = $dialect;
    }

    public function execute(): ?Results
    {
        [$query, $params] = $this->build();

        if (preg_match('/^CREATE|ALTER|DROP/', $query)) {
            $this->database->unsafe($query);

            return null;
        }

        return $this->database->safe($query, $params);
    }

    public function rawQuery(): string
    {
        [$query, $params] = $this->build();

        return $this->dialect->toRawQuery($query, $params);
    }

    public static function alias(string|array|Raw $name, string $alias): Alias
    {
        return new Alias($name, $alias);
    }

    public static function raw(string $expression): Raw
    {
        return new Raw($expression);
    }

    public static function now(): DateTime
    {
        return new DateTime();
    }

    public static function wildcard(string $string): string
    {
        $chars = ['%', '_', '-', '^', '[', ']'];

        return '%' . escape_chars($string, $chars) . '%';
    }
}
