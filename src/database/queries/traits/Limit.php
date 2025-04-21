<?php

namespace src\database\queries\traits;

trait Limit
{
    protected ?int $limit = null;

    public function limit(int $limit): static
    {
        $this->limit = ($limit > 0) ? $limit : null;

        return $this;
    }
}
