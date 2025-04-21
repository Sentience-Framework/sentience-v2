<?php

namespace src\database\queries\traits;

trait Returning
{
    protected ?array $returning = null;

    public function returning(array $columns = []): static
    {
        $this->returning = $columns;

        return $this;
    }
}
