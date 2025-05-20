<?php

namespace src\database\queries;

use src\database\queries\objects\AddColumn;
use src\database\queries\objects\AddForeignKeyConstraint;
use src\database\queries\objects\AddUniqueConstraint;
use src\database\queries\objects\AlterColumn;
use src\database\queries\objects\DropColumn;
use src\database\queries\objects\DropConstraint;
use src\database\queries\objects\RenameColumn;
use src\database\queries\traits\Table;

class AlterTable extends Query implements QueryInterface
{
    use Table;

    protected array $alters = [];

    public function build(): array
    {
        return $this->dialect->alterTable([
            'table' => $this->table,
            'alters' => $this->alters
        ]);
    }

    public function addColumn(string $name, string $type, bool $notNull = false, mixed $defaultValue = null, bool $autoIncrement = false): static
    {
        $this->alters[] = new AddColumn($name, $type, $notNull, $defaultValue, $autoIncrement);

        return $this;
    }

    public function alterColumn(string $column, string $options): static
    {
        $this->alters[] = new AlterColumn($column, $options);

        return $this;
    }

    public function renameColumn(string $oldName, string $newName): static
    {
        $this->alters[] = new RenameColumn($oldName, $newName);

        return $this;
    }

    public function dropColumn(string $column): static
    {
        $this->alters[] = new DropColumn($column);

        return $this;
    }

    public function addUniqueConstraint(array $columns, ?string $name): static
    {
        $this->alters[] = new AddUniqueConstraint($columns, $name);

        return $this;
    }

    public function addForeignKeyConstraint(string $column, string $referenceTable, string $referenceColumn, ?string $name = null): static
    {
        $this->alters[] = new AddForeignKeyConstraint($column, $referenceTable, $referenceColumn, $name);

        return $this;
    }

    public function dropConstraint(string $constraint): static
    {
        $this->alters[] = new DropConstraint($constraint);

        return $this;
    }
}
