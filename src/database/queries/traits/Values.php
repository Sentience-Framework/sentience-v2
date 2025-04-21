<?php

namespace src\database\queries\traits;

trait Values
{
    protected array $values = [];

    public function values(array $values): static
    {
        $this->values = $values;

        return $this;
    }
}
