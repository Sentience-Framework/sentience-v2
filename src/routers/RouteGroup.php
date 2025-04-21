<?php

namespace src\routers;

class RouteGroup
{
    public string $route;
    /**
     * @var Route|RouteGroup[] $routes
     */
    public array $routes = [];
    public array $middleware = [];

    public static function create(string $route): static
    {
        return new static($route);
    }

    public function __construct(string $route)
    {
        $this->route = trim($route, '/');
    }

    public function bind(Route|RouteGroup $route): static
    {
        $this->routes[] = $route;

        return $this;
    }

    public function setMiddleware(array $middleware): static
    {
        $this->middleware = $middleware;

        return $this;
    }
}
