<?php

namespace src\database\dialects;

use DateTime;
use src\database\queries\containers\Raw;

interface DialectInterface
{
    public function select(array $config): array;
    public function insert(array $config): array;
    public function update(array $config): array;
    public function delete(array $config): array;
    public function createTable(array $config): array;
    public function alterTable(array $config): array;
    public function dropTable(array $config): array;
    public function escapeIdentifier(string|array|Raw $identifier): string;
    public function escapeString(string $string): string;
    public function castToDriver(mixed $value): mixed;
    public function castToQuery(mixed $value): mixed;
    public function castBool(bool $bool): mixed;
    public function castDateTime(DateTime $dateTime): mixed;
    public function parseBool(mixed $bool): bool;
    public function parseDateTime(string $dateTimeString): ?DateTime;
    public function phpTypeToColumnType(string $type, bool $isAutoIncrement, bool $isPrimaryKey, bool $inConstraint): string;
    public function toRawQuery(string $query, array $params): string;
}
