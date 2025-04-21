<?php

namespace src\database\queries\traits;

use src\database\queries\containers\Alias;
use src\database\queries\containers\Raw;

trait Table
{
    protected string|array|Alias|raw $table = '';

    public function table(string|array|Alias|Raw $table): static
    {
        $this->table = $table;

        return $this;
    }
}
