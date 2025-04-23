<?php

namespace src\database\dialects;

use DateTime;
use src\database\queries\containers\Alias;
use src\database\queries\containers\Raw;
use src\database\queries\definitions\AddColumn;
use src\database\queries\definitions\AlterColumn;
use src\database\queries\definitions\Column;
use src\database\queries\definitions\DropColumn;
use src\database\queries\definitions\ForeignKeyConstraint;
use src\database\queries\definitions\RenameColumn;
use src\database\queries\definitions\UniqueConstraint;

interface DialectInterface
{
    public function addTable(string &$query, string|array|Alias|Raw $table, bool $allowAlias = false): void;
    public function addJoins(string &$query, array $joins): void;
    public function addWhere(string &$query, array &$params, array $where): void;
    public function addGroupBy(string &$query, array $groupBy): void;
    public function addOrderBy(string &$query, array $orderBy): void;
    public function addLimit(string &$query, ?int $limit): void;
    public function addOffset(string &$query, ?int $limit, ?int $offset): void;
    public function addConflict(string &$query, array &$params, null|string|array $conflict, ?array $conflictUpdates, array $values, ?string $primaryKey): void;
    public function addReturning(string &$query, ?array $columns): void;
    public function stringifyColumnDefinition(Column $column): string;
    public function stringifyUniqueConstraintDefinition(UniqueConstraint $uniqueConstraint): string;
    public function stringifyForeignKeyConstraintDefinition(ForeignKeyConstraint $foreignKeyConstraint): string;
    public function stringifyAlterTableAddColumn(AddColumn $addColumn): string;
    public function stringifyAlterTableAlterColumn(AlterColumn $alterColumn): string;
    public function stringifyAlterTableRenameColumn(RenameColumn $renameColumn): string;
    public function stringifyAlterTableDropColumn(DropColumn $dropColumn): string;
    public function phpTypeToColumnType(string $type, bool $isAutoIncrement, bool $isPrimaryKey, bool $inConstraint): string;
    public function escapeTableOrColumn(string|array|Raw $references, ?string $alias = null): string;
    public function escapeString(string $string): string;
    public function castToDriver(mixed $value): mixed;
    public function castToQuery(mixed $value): mixed;
    public function castBool(bool $bool): mixed;
    public function castDateTime(DateTime $dateTime): mixed;
    public function parseBool(mixed $bool): bool;
    public function parseDateTime(string $dateTimeString): ?DateTime;
    public function toRawQuery(string $query, array $params): string;
}
