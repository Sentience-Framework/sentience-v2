<?php

namespace src\database\queries\traits;

use src\database\queries\objects\Alias;
use src\database\queries\objects\Raw;

trait Table
{
    protected null|string|array|Alias|Raw $table = null;

    public function table(string|array|Alias|Raw $table): static
    {
        $this->table = $table;

        return $this;
    }
}
