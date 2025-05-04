<?php

namespace src\database\queries;

use src\database\Results;

interface QueryInterface
{
    public function build(): array;
    public function execute(): ?Results;
    public function tryCatch(): ?Results;
    public function rawQuery(): string;
}
