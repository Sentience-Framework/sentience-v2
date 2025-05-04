<?php

namespace src\database\queries\traits;

use DateTime;

trait Having
{
    protected ?string $havingExpression = null;
    protected array $havingValues = [];

    public function having(string $expression, bool|int|float|string|DateTime ...$values): static
    {
        $this->havingExpression = $expression;
        $this->havingValues = $values;

        return $this;
    }
}
