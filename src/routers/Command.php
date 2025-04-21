<?php

namespace src\routers;

class Command
{
    public string $command;
    public mixed $callback;
    public array $middleware = [];

    public static function create(string $command): static
    {
        return new static($command);
    }

    public function __construct(string $command)
    {
        $this->command = $command;
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
