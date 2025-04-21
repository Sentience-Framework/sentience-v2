<?php

namespace src\sentience;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Service;
use Throwable;
use src\controllers\Controller;
use src\middleware\Middleware;
use src\exceptions\CallbackException;
use src\exceptions\DeprecatedException;
use src\exceptions\FatalErrorException;
use src\exceptions\NoticeException;
use src\exceptions\WarningException;
use src\routers\CliRouter;
use src\routers\Command;
use src\routers\HttpRouter;
use src\routers\Route;
use src\routers\RouteGroup;
use src\utils\Reflector;
use src\utils\Terminal;

class Sentience
{
    protected CliRouter $cliRouter;
    protected HttpRouter $httpRouter;
    protected Service $service;

    public function __construct()
    {
        $this->cliRouter = new CliRouter();
        $this->httpRouter = new HttpRouter();
        $this->service = new Service();
    }

    public function bindCommand(Command $command): static
    {
        $this->cliRouter->bind($command);

        return $this;
    }

    public function bindRoute(Route|RouteGroup $route): static
    {
        $this->httpRouter->bind($route);

        return $this;
    }

    public function execute(): void
    {
        error_reporting(0);

        register_shutdown_function(
            function (): void {
                $error = error_get_last();

                if (!$error) {
                    return;
                }

                $message = $error['message'];
                $severity = $error['type'];
                $file = $error['type'];
                $line = $error['line'];

                $exception = new FatalErrorException($message, 0, $severity, $file, $line);

                $this->handleException($exception);
            }
        );

        set_error_handler(
            function (int $s, string $m, string $f, int $l): bool {
                if (in_array($s, [E_NOTICE, E_USER_NOTICE])) {
                    throw new NoticeException($m, 0, $s, $f, $l);
                }

                if (in_array($s, [E_WARNING, E_COMPILE_WARNING, E_CORE_WARNING, E_USER_WARNING])) {
                    throw new WarningException($m, 0, $s, $f, $l);
                }

                if (in_array($s, [E_DEPRECATED, E_USER_DEPRECATED])) {
                    throw new DeprecatedException($m, 0, $s, $f, $l);
                }

                return false;
            }
        );

        try {
            is_cli()
                ? $this->executeCli()
                : $this->executeHttp();
        } catch (Throwable $exception) {
            $this->handleException($exception);
        }
    }

    protected function executeCli(): void
    {
        $argv = new Argv();

        [$command, $words, $flags] = $this->cliRouter->match($argv);

        if (!$command) {
            $this->cliNotFound($argv);

            return;
        }

        $this->executeCallback(callback: $command->callback, middleware: $command->middleware, words: $words, flags: $flags);
    }

    protected function executeHttp(): void
    {
        $request = new Request();

        [$route, $pathVars, $statusCode] = $this->httpRouter->match($request);

        if ($statusCode) {
            if ($statusCode == 404) {
                $this->httpNotFound($request);

                return;
            }

            if ($statusCode == 405) {
                $this->httpMethodNotAllowed($request);

                return;
            }
        }

        if ($pathVars) {
            $request->pathVars = $pathVars;
        }

        $this->executeCallback(callback: $route->callback, middleware: $route->middleware, request: $request);
    }

    protected function executeCallback(string|array|callable $callback, array $middleware, array $words = [], array $flags = [], ?Request $request = null): void
    {
        $this->validateCallback($callback);

        $args = $this->executeMiddleware($middleware, $words, $flags, $request);

        if (is_array($callback)) {
            [$callback, $args] = $this->constructCallbackClass($callback, $args, $words, $flags, $request);
        }

        $callbackReflector = is_array($callback)
            ? new ReflectionMethod(...$callback)
            : new ReflectionFunction($callback);

        $filteredArgs = $this->filterArgs($callbackReflector, $args, $words, $flags, $request);

        $callback(...$filteredArgs);
    }

    protected function executeMiddleware(array $middleware, array $words, array $flags, ?Request $request): ?array
    {
        $args = [];

        foreach ($middleware as $callback) {
            $this->validateCallback($callback);

            if (is_array($callback)) {
                [$callback, $args] = $this->constructCallbackClass($callback, $args, $words, $flags, $request);
            }

            $args = $this->executeMiddlewareCallback($callback, $args, $words, $flags, $request);
        }

        return $args;
    }

    protected function executeMiddlewareCallback(string|array|callable $callback, array $args, array $words, array $flags, ?Request $request): array
    {
        $callbackReflector = is_array($callback)
            ? new ReflectionMethod(...$callback)
            : new ReflectionFunction($callback);

        $filteredArgs = $this->filterArgs($callbackReflector, $args, $words, $flags, $request);

        $middlewareArgs = $callback(...$filteredArgs);

        if (!is_array($middlewareArgs)) {
            return $args;
        }

        return [...$args, ...$middlewareArgs];
    }

    protected function validateCallback(string|array|callable $callback): void
    {
        if ($callback instanceof Closure) {
            return;
        }

        if (is_array($callback)) {
            [$class, $method] = $callback;

            if (!class_exists($class)) {
                throw new CallbackException('class %s does not exist"', $class);
            }

            if (!method_exists($class, $method)) {
                throw new CallbackException('class %s does not have method %s', $class, $method);
            }

            return;
        }

        if (!function_exists($callback)) {
            throw new CallbackException('function %s does not exist', $callback);
        }
    }

    protected function constructCallbackClass(array $callback, array $args, array $words, array $flags, ?Request $request): array
    {
        [$class, $method] = $callback;

        $reflectionClass = new ReflectionClass($class);

        $isSubclassOfController = $reflectionClass->isSubclassOf(Controller::class);
        $isSubclassOfMiddleware = $reflectionClass->isSubclassOf(Middleware::class);

        if (!in_array(true, [$isSubclassOfController, $isSubclassOfMiddleware])) {
            throw new CallbackException('class %s does not extend a supported base class', $class);
        }

        $constructorReflector = $reflectionClass->getConstructor();

        $filteredArgs = $constructorReflector ? $this->filterArgs($constructorReflector, $args, $words, $flags, $request) : [];

        $callback = [new $class(...$filteredArgs), $method];

        $args = [...$args, ...$filteredArgs];

        return [$callback, $args];
    }

    protected function filterArgs(ReflectionFunctionAbstract $callbackReflector, array $args, array $words, array $flags, ?Request $request): array
    {
        $callbackArgs = $callbackReflector->getParameters();

        $serviceMethods = get_class_methods($this->service);

        $filteredArgs = [];

        foreach ($callbackArgs as $callbackArg) {
            $name = $callbackArg->getName();

            if ($callbackArg->isVariadic()) {
                $filteredArgs[$name] = array_filter(
                    $args,
                    function (string $arg) use ($filteredArgs): bool {
                        return !key_exists($arg, $filteredArgs);
                    },
                    ARRAY_FILTER_USE_KEY
                );
                continue;
            }

            if (key_exists($name, $args)) {
                $filteredArgs[$name] = $args[$name];
                continue;
            }

            if ($name == 'words') {
                $filteredArgs['words'] = $words;
                continue;
            }

            if ($name == 'flags') {
                $filteredArgs['flags'] = $flags;
                continue;
            }

            if ($name == 'request') {
                $filteredArgs['request'] = $request;
                continue;
            }

            if (in_array($name, $serviceMethods)) {
                $serviceMethod = [$this->service, $name];
                $filteredArgs[$name] = $serviceMethod();
                continue;
            }

            if ($callbackArg->isDefaultValueAvailable()) {
                $filteredArgs[$name] = $callbackArg->getDefaultValue();
                continue;
            }

            $filteredArgs[$name] = null;
        }

        return $filteredArgs;
    }

    protected function handleException(Throwable $exception): void
    {
        is_cli()
            ? $this->handleExceptionCli($exception)
            : $this->handleExceptionHttp($exception);
    }

    protected function handleExceptionCli(Throwable $exception): void
    {
        $terminalWidth = Terminal::getWidth();

        $equalSigns = ($terminalWidth - 9) / 2 - 1;

        Stdio::errorFLn(
            '%s Exception %s',
            str_repeat('=', ceil($equalSigns)),
            str_repeat('=', floor($equalSigns))
        );

        Stdio::errorFLn('- Text  : %s', $exception->getMessage());
        Stdio::errorFLn('- Type  : %s', Reflector::getShortName($exception));
        Stdio::errorFLn('- File  : %s', $exception->getFile());
        Stdio::errorFLn('- Line  : %s', $exception->getLine());

        if (env('APP_STACK_TRACE', false)) {
            $stackTrace = array_values(
                array_filter(
                    $exception->getTrace(),
                    function (array $frame): bool {
                        return key_exists('file', $frame);
                    }
                )
            );

            if (count($stackTrace) > 0) {
                Stdio::errorLn('- Trace :');

                foreach ($stackTrace as $index => $frame) {
                    if (!key_exists('file', $frame)) {
                        continue;
                    }

                    $file = $frame['file'];
                    $line = $frame['line'];

                    $function = key_exists('class', $frame)
                        ? $frame['class'] . $frame['type'] . $frame['function']
                        : $frame['function'];

                    $args = implode(
                        ', ',
                        array_map(
                            function (mixed $arg): string {
                                return get_debug_type($arg);
                            },
                            $frame['args'] ?? []
                        )
                    );

                    Stdio::errorFLn(
                        '      %s : %s:%s %s(%s)',
                        $index + 1,
                        $file,
                        $line,
                        $function,
                        $args
                    );
                }
            }
        }

        Stdio::errorLn(str_repeat('=', $terminalWidth));

        exit;
    }

    protected function handleExceptionHttp(Throwable $exception): void
    {
        $response = [
            'error' => [
                'type' => Reflector::getShortName($exception),
                'message' => $exception->getMessage(),
            ]
        ];

        if (env('APP_STACK_TRACE', false)) {
            $response['error']['file'] = $exception->getFile();
            $response['error']['line'] = $exception->getLine();

            $stackTrace = array_values(
                array_filter(
                    array_map(
                        function (array $frame): ?array {
                            if (!key_exists('file', $frame)) {
                                return null;
                            }

                            $file = $frame['file'];
                            $line = $frame['line'];

                            $function = key_exists('class', $frame)
                                ? $frame['class'] . $frame['type'] . $frame['function']
                                : $frame['function'];

                            $args = array_map(
                                function (mixed $arg): string {
                                    return get_debug_type($arg);
                                },
                                $frame['args'] ?? []
                            );

                            return [
                                'file' => $file,
                                'line' => $line,
                                'function' => $function,
                                'args' => $args
                            ];
                        },
                        $exception->getTrace()
                    )
                )
            );

            if (count($stackTrace) > 0) {
                $response['error']['trace'] = $stackTrace;
            }
        }

        Response::internalServerError($response);
    }

    protected function cliNotFound(Argv $argv): void
    {
        $terminalWidth = Terminal::getWidth();

        $equalSigns = ($terminalWidth - 17) / 2 - 1;

        Stdio::errorFLn(
            '%s Command not found %s',
            str_repeat('=', ceil($equalSigns)),
            str_repeat('=', floor($equalSigns))
        );

        foreach ($this->cliRouter->commands as $command) {
            Stdio::errorFLn('- %s', $command->command);
        }

        Stdio::errorLn(str_repeat('=', $terminalWidth));

        exit;
    }

    protected function httpNotFound(Request $request): void
    {
        Response::notFound([
            'error' => [
                'type' => 'HttpException',
                'message' => 'resource not found'
            ]
        ]);
    }

    protected function httpMethodNotAllowed(Request $request): void
    {
        Response::methodNotAllowed([
            'error' => [
                'type' => 'HttpException',
                'message' => 'method not allowed'
            ]
        ]);
    }
}
