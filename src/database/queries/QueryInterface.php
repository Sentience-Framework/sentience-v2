<?php

namespace src\database\queries;

use src\database\queries\objects\QueryWithParams;
use src\database\Results;

interface QueryInterface
{
    public function build(): QueryWithParams;
    public function execute(): ?Results;
    public function tryCatch(): ?Results;
    public function toRawQuery(): string;
}
