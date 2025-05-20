<?php

namespace src\database\queries\traits;

use src\database\queries\containers\OrderBy as OrderByContainer;
use src\database\queries\containers\Raw;
use src\database\queries\enums\OrderByDirection;

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
