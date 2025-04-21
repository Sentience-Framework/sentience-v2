<?php

namespace src\routers;

class Route
{
    public string $route;
    public mixed $callback;
    public array $methods = ['*'];
    public array $middleware = [];

    public static function create(string $route): static
    {
        return new static($route);
    }

    public function __construct(string $route)
    {
        $this->route = trim($route, '/');
    }

    public function setCallback(string|array|callable $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    public function setMethods(array $methods): static
    {
        $this->methods = array_map(
            function (string $method): string {
                return strtoupper($method);
            },
            $methods
        );

        return $this;
    }

    public function setMiddleware(array $middleware): static
    {
        $this->middleware = $middleware;

        return $this;
    }
}
