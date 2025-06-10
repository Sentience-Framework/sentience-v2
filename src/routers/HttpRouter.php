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
            '/{(.[^\}]*)}/',
            function (array $matches) use (&$keys): string {
                $keys[] = $matches[1];

                return '(.[^\/]*)';
            },
            sprintf(
                '/^%s$/',
                escape_chars($route, ['.', '/', '+', '*', '?', '^', '[', ']', '$', '(', ')', '=', '!', '<', '>', '|', ':', '-'])
            )
        );

        $isMatch = preg_match($pattern, $path, $matches);

        if (!$isMatch) {
            return [false, null];
        }

        $values = array_splice($matches, 1);

        $values = array_map(
            function (string $value): string {
                return urldecode($value);
            },
            $values
        );

        $pathVars = array_combine($keys, $values);

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
            if (!key_exists($route->route, $mappedRoutes)) {
                $mappedRoutes[$route->route] = [];
            }

            if ($route instanceof Route) {
                foreach ($route->methods as $method) {
                    $mappedRoutes[$route->route][$method] = $route;
                }

                continue;
            }

            if ($route instanceof RouteGroup) {
                $mappedRouteGroupRoutes = $this->mapRouteGroup($route, [], []);

                foreach ($mappedRouteGroupRoutes as $mappedRouteGroupRoute => $methods) {
                    if (key_exists($mappedRouteGroupRoute, $mappedRoutes)) {
                        $existingMethods = $mappedRoutes[$mappedRouteGroupRoute];

                        $mappedRoutes[$mappedRouteGroupRoute] = [
                            ...$existingMethods,
                            ...$methods
                        ];

                        continue;
                    }

                    $mappedRoutes = [...$mappedRoutes, $mappedRouteGroupRoute => $methods];
                }
            }
        }

        return array_filter(
            $mappedRoutes,
            function (array $methods): bool {
                return count($methods) > 0;
            }
        );
    }

    protected function mapRouteGroup(RouteGroup $routeGroup, array $prefixes, array $middleware): array
    {
        $prefixes[] = $routeGroup->route;
        $middleware = [...$middleware, ...$routeGroup->middleware];

        $mappedRoutes = [];

        foreach ($routeGroup->routes as $route) {
            $routeWithPrefixes = trim(
                implode(
                    '/',
                    [
                        ...$prefixes,
                        $route->route
                    ]
                ),
                '/'
            );

            if (!key_exists($routeWithPrefixes, $mappedRoutes)) {
                $mappedRoutes[$routeWithPrefixes] = [];
            }

            if ($route instanceof Route) {
                $routeMiddleware = $route->middleware;

                $route->setMiddleware([
                    ...$middleware,
                    ...$routeMiddleware
                ]);

                foreach ($route->methods as $method) {
                    $mappedRoutes[$routeWithPrefixes][$method] = $route;
                }

                continue;
            }

            if ($route instanceof RouteGroup) {
                $mappedRouteGroupRoutes = $this->mapRouteGroup($route, $prefixes, $middleware);

                foreach ($mappedRouteGroupRoutes as $mappedRouteGroupRoute => $methods) {
                    if (key_exists($mappedRouteGroupRoute, $mappedRoutes)) {
                        $existingMethods = $mappedRoutes[$mappedRouteGroupRoute];

                        $mappedRoutes[$mappedRouteGroupRoute] = [
                            ...$existingMethods,
                            ...$methods
                        ];

                        continue;
                    }

                    $mappedRoutes = [...$mappedRoutes, $mappedRouteGroupRoute => $methods];
                }
            }
        }

        return $mappedRoutes;
    }
}
