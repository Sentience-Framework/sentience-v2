<?php

namespace src\database\queries\traits;

use src\database\queries\containers\Alias;
use src\database\queries\containers\Join;
use src\database\queries\containers\Raw;
use src\database\queries\enums\JoinType;
use src\database\queries\Query;

trait Joins
{
    protected array $joins = [];

    public function leftJoin(string|array|Alias|Raw $joinTable, string $joinTableColumn, string|array|Raw $onTable, string $onTableColumn): static
    {
        $this->addJoin(JoinType::LEFT_JOIN, $joinTable, $joinTableColumn, $onTable, $onTableColumn);

        return $this;
    }

    public function rightJoin(string|array|Alias|Raw $joinTable, string $joinTableColumn, string|array|Raw $onTable, string $onTableColumn): static
    {
        $this->addJoin(JoinType::RIGHT_JOIN, $joinTable, $joinTableColumn, $onTable, $onTableColumn);

        return $this;
    }

    public function innerJoin(string|array|Alias|Raw $joinTable, string $joinTableColumn, string|array|Raw $onTable, string $onTableColumn): static
    {
        $this->addJoin(JoinType::INNER_JOIN, $joinTable, $joinTableColumn, $onTable, $onTableColumn);

        return $this;
    }

    public function join(string $expression): static
    {
        $this->joins[] = Query::raw($expression);

        return $this;
    }

    protected function addJoin(JoinType $type, string|array|Alias|Raw $joinTable, string $joinTableColumn, string|array|Raw $onTable, string $onTableColumn): void
    {
        $this->joins[] = new Join($type, $joinTable, $joinTableColumn, $onTable, $onTableColumn);
    }
}
