<?php

namespace src\database\queries\traits;

trait Columns
{
    protected array $columns = [];

    public function columns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }
}
