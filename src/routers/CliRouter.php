<?php

namespace src\routers;

use src\sentience\Argv;

class CliRouter
{
    /**
     * @var Command[] $commands
     */
    public array $commands = [];

    public function __construct()
    {
    }

    public function bind(Command $command): void
    {
        $this->commands[] = $command;
    }

    public function match(Argv $argv): array
    {
        $cmd = $argv->command;

        foreach ($this->commands as $command) {
            if ($cmd == $command->command) {
                [$words, $flags] = $this->parseArgs($argv->args);

                return [$command, $words, $flags];
            }
        }

        return [null, null, null];
    }

    protected function parseArgs(array $args): array
    {
        $words = [];
        $flags = [];

        foreach ($args as $arg) {
            $isSyntaxMatch = preg_match('/--(.[^=]*)=?(.*)/', $arg, $matches);

            if (!$isSyntaxMatch) {
                $words[] = $arg;
                continue;
            }

            $key = $matches[1];
            $value = $matches[2] ?? '';

            $flags[$key] = $value;
        }

        return [$words, $flags];
    }
}
