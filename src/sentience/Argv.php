<?php

namespace src\sentience;

class Argv
{
    public string $file;
    public string $command;
    public array $args;

    public function __construct()
    {
        $argv = $GLOBALS['argv'];

        $this->file = $argv[0];

        $this->command = $argv[1] ?? '';

        $this->args = count($argv) > 2
            ? array_slice($argv, 2)
            : [];
    }
}
