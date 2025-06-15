<?php

namespace src\routers;

class Command
{
    public string $command;
    public mixed $callback;
    public array $middleware = [];

    public static function register(string $command, string|array|callable $callback): static
    {
        return new static($command, $callback);
    }

    public function __construct(string $command, string|array|callable $callback)
    {
        $this->setCommand($command);
        $this->setCallback($callback);
    }

    public function setCommand(string $command): static
    {
        $this->command = $command;

        return $this;
    }

    public function setCallback(string|array|callable $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    public function setMiddleware(array $middleware): static
    {
        $this->middleware = $middleware;

        return $this;
    }
}
