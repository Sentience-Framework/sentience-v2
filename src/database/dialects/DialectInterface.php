<?php

namespace src\database\dialects;

use DateTime;
use src\database\queries\objects\QueryWithParams;
use src\database\queries\objects\Raw;

interface DialectInterface
{
    public function select(array $config): QueryWithParams;
    public function insert(array $config): QueryWithParams;
    public function update(array $config): QueryWithParams;
    public function delete(array $config): QueryWithParams;
    public function createTable(array $config): QueryWithParams;
    public function alterTable(array $config): QueryWithParams;
    public function dropTable(array $config): QueryWithParams;
    public function escapeIdentifier(string|array|Raw $identifier): string;
    public function escapeString(string $string): string;
    public function castToDriver(mixed $value): mixed;
    public function castToQuery(mixed $value): mixed;
    public function castBool(bool $bool): mixed;
    public function castDateTime(DateTime $dateTime): mixed;
    public function parseBool(mixed $bool): bool;
    public function parseDateTime(string $dateTimeString): ?DateTime;
    public function phpTypeToColumnType(string $type, bool $isAutoIncrement, bool $isPrimaryKey, bool $inConstraint): string;
}
