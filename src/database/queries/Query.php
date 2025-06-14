<?php

namespace src\database\queries;

use DateTime;
use Throwable;
use src\database\Database;
use src\database\dialects\DialectInterface;
use src\database\queries\objects\Alias;
use src\database\queries\objects\Raw;
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
        $queryWithParams = $this->build();

        if (preg_match('/^CREATE|ALTER|DROP/', $queryWithParams->expression)) {
            $this->database->unsafe($queryWithParams->expression);

            return null;
        }

        return $this->database->safe(
            $queryWithParams->expression,
            $queryWithParams->params
        );
    }

    public function tryCatch(?callable $handleException = null): ?Results
    {
        try {
            return $this->execute();
        } catch (Throwable $exception) {
            if ($handleException) {
                $handleException($exception);
            }

            return null;
        }
    }

    public function rawQuery(): string
    {
        return $this->build()->toRawQuery($this->dialect);
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

    public static function escapeLikeChars(string $string, bool $escapeBackslash = false): string
    {
        $chars = ['%', '_', '-', '^', '[', ']'];

        if ($escapeBackslash) {
            array_unshift($chars, '\\');
        }

        return escape_chars($string, $chars);
    }

    public static function wildcard(string $string, bool $escapeBackslash = false): string
    {
        return '%' . static::escapeLikeChars($string, $escapeBackslash) . '%';
    }
}
