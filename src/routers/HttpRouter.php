<?php

namespace src\routers;

use src\sentience\Request;

class HttpRouter
{
    public array $routes = [];

    public function __construct()
    {
    }

    public function bind(Route|RouteGroup $route): void
    {
        $this->routes[] = $route;
    }

    public function match(Request $request): array
    {
        $path = $request->path;
        $method = $request->method;

        $mappedRoutes = $this->mapRoutes();

        foreach ($mappedRoutes as $route => $methods) {
            [$isMatch, $pathVars] = $this->isMatch($path, $route);

            if (!$isMatch) {
                continue;
            }

            if (key_exists('*', $methods)) {
                $route = $methods['*'];

                return [$route, $pathVars, null];
            }

            if (!key_exists($method, $methods)) {
                return [null, null, 405];
            }

            $route = $methods[$method];

            return [$route, $pathVars, null];
        }

        return [null, null, 404];
    }

    protected function isMatch(string $path, string $route): array
    {
        $path = trim($path, '/');
        $route = trim($route, '/');

        if ($path == $route) {
            return [true, null];
        }

        $keys = [];

        $pattern = preg_replace_callback(
            '/\{([^\:\}]+)(?:\:([^\}]+))?\}/',
            function (array $matches) use (&$keys): string {
                $key = $matches[1];
                $type = $matches[2] ?? 'string';

                $keys[] = [$key, $type];

                return '(.[^\/]*)';
            },
            sprintf(
                '/^%s$/',
                escape_chars($route, ['.', '/', '+', '*', '?', '^', '[', ']', '$', '(', ')', '=', '!', '<', '>', '|', '-'])
            )
        );

        $isMatch = preg_match($pattern, $path, $matches);

        if (!$isMatch) {
            return [false, null];
        }

        $values = array_slice($matches, 1);

        $pathVars = [];

        foreach ($values as $index => $value) {
            [$key, $type] = $keys[$index];

            $pathVars[$key] = match ($type) {
                'int' => (int) $value,
                'float' => (float) $value,
                default => urldecode($value)
            };
        }

        return [true, $pathVars];
    }

    protected function mapRoutes(): array
    {
        /**
         * Structure:
         *
         * [
         *      'path/to/resource' => [
         *          'method1' => Route,
         *          'method2' => Route
         *      ]
         * ]
         */

        $mappedRoutes = [];

        foreach ($this->routes as $route) {
            $route instanceof Route
                ? $this->addToMap($mappedRoutes, $route)
                : $this->mapRouteGroup($mappedRoutes, $route, [], []);
        }

        ksort($mappedRoutes);

        return $mappedRoutes;
    }

    protected function mapRouteGroup(array &$mappedRoutes, RouteGroup $routeGroup, array $prefixes, array $middleware): array
    {
        $prefixes = [...$prefixes, $routeGroup->route];
        $middleware = [...$middleware, ...$routeGroup->middleware];

        foreach ($routeGroup->routes as $route) {
            $routeWithPrefixes = implode(
                '/',
                array_filter([
                    ...$prefixes,
                    $route->route
                ])
            );

            $route instanceof Route
                ? $this->addToMap($mappedRoutes, $route, $middleware, $routeWithPrefixes)
                : $this->mapRouteGroup($mappedRoutes, $route, $prefixes, $middleware);
        }

        return $mappedRoutes;
    }

    protected function addToMap(array &$mappedRoutes, Route $route, array $middleware = [], ?string $routeWithPrefixes = null): void
    {
        $key = $routeWithPrefixes ?? $route->route;

        if (!key_exists($key, $mappedRoutes)) {
            $mappedRoutes[$key] = [];
        }

        $route->setMiddleware([
            ...$middleware,
            ...$route->middleware
        ]);

        foreach ($route->methods as $method) {
            $mappedRoutes[$key][$method] = $route;
        }
    }
}
