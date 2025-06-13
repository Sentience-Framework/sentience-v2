<?php

namespace src\routers;

class RouteGroup
{
    public string $route;
    public array $routes = [];
    public array $middleware = [];

    public static function register(string $route): static
    {
        return new static($route);
    }

    public function __construct(string $route)
    {
        $this->setRoute($route);
    }

    public function bind(Route|RouteGroup $route): static
    {
        $this->routes[] = $route;

        return $this;
    }

    public function setRoute(string $route): static
    {
        $this->route = trim($route, '/');

        return $this;
    }

    public function setMiddleware(array $middleware): static
    {
        $this->middleware = $middleware;

        return $this;
    }
}
