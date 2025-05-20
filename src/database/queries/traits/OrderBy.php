<?php

namespace src\database\queries\traits;

use src\database\queries\enums\OrderByDirection;
use src\database\queries\objects\OrderBy as OrderByContainer;
use src\database\queries\objects\Raw;

trait OrderBy
{
    protected array $orderBy = [];

    public function orderByAsc(string|array|Raw $column): static
    {
        $this->addOrderBy($column, OrderByDirection::ASC);

        return $this;
    }

    public function orderByDesc(string|array|Raw $column): static
    {
        $this->addOrderBy($column, OrderByDirection::DESC);

        return $this;
    }

    protected function addOrderBy(string|array|Raw $column, OrderByDirection $direction): void
    {
        $this->orderBy[] = new OrderByContainer($column, $direction);
    }
}
