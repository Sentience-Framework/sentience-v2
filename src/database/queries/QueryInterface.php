<?php

namespace src\database\queries;

use src\database\Results;

interface QueryInterface
{
    public function build(): array;
    public function execute(): int|Results;
    public function rawQuery(): string;
}
