<?php

namespace src\database\queries\traits;

trait Offset
{
    protected ?int $offset = null;

    public function offset(int $offset): static
    {
        $this->offset = ($offset > 0) ? $offset : null;

        return $this;
    }
}
