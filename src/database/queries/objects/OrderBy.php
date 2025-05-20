<?php

namespace src\database\queries\objects;

use src\database\queries\enums\OrderByDirection;

class OrderBy
{
    public string|array|Raw $column;
    public OrderByDirection $direction;

    public function __construct(string|array|Raw $column, OrderByDirection $direction)
    {
        $this->column = $column;
        $this->direction = $direction;
    }
}
