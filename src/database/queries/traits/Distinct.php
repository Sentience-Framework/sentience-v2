<?php

namespace src\database\queries\traits;

trait Distinct
{
    protected bool $distinct = false;

    public function distinct(): static
    {
        $this->distinct = true;

        return $this;
    }
}
