<?php

namespace src\database\queries;

use src\database\queries\definitions\AddColumn;
use src\database\queries\definitions\AlterColumn;
use src\database\queries\definitions\DropColumn;
use src\database\queries\definitions\RenameColumn;
use src\database\queries\traits\Table;

class AlterTable extends Query implements QueryInterface
{
    use Table;

    protected array $alters = [];

    public function build(): array
    {
        $query = '';
        $params = [];

        $queries = [];

        foreach ($this->alters as $alter) {
            if ($alter instanceof AddColumn) {
                $queries[] = $this->buildAlterTableQuery(
                    $this->dialect->stringifyAlterTableAddColumn($alter)
                );
            }

            if ($alter instanceof AlterColumn) {
                $queries[] = $this->buildAlterTableQuery(
                    $this->dialect->stringifyAlterTableAlterColumn($alter)
                );
            }

            if ($alter instanceof RenameColumn) {
                $queries[] = $this->buildAlterTableQuery(
                    $this->dialect->stringifyAlterTableRenameColumn($alter)
                );
            }

            if ($alter instanceof DropColumn) {
                $queries[] = $this->buildAlterTableQuery(
                    $this->dialect->stringifyAlterTableDropColumn($alter)
                );
            }
        }

        $query .= implode(' ', $queries);

        return [$query, $params];
    }

    protected function buildAlterTableQuery(string $stringified): string
    {
        $query = 'ALTER TABLE';

        $this->dialect->addTable($query, $this->table);

        $query .= ' ';
        $query .= $stringified;
        $query .= ';';

        return $query;
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
}
