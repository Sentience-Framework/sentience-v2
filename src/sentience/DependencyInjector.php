<?php

namespace src\sentience;

use ReflectionFunctionAbstract;
use src\exceptions\DependencyInjectionException;

class DependencyInjector
{
    protected array $injectables;
    protected ?object $service;

    public function __construct(array $injectables, ?object $service = null)
    {
        $this->injectables = $injectables;
        $this->service = $service;
    }

    public function getFunctionParameters(ReflectionFunctionAbstract $reflectionFunctionAbstract, array $injectables = []): array
    {
        $injectables = [
            ...$this->injectables,
            ...$injectables
        ];

        $serviceProperties = $this->service
            ? get_object_vars($this->service)
            : [];

        $serviceMethods = $this->service
            ? get_class_methods($this->service)
            : [];

        $functionParameters = $reflectionFunctionAbstract->getParameters();

        $parameters = [];

        foreach ($functionParameters as $functionParameter) {
            $name = $functionParameter->getName();

            if (key_exists($name, $injectables)) {
                $parameters[$name] = $injectables[$name];

                continue;
            }

            if (key_exists($name, $serviceProperties)) {
                $parameters[$name] = $serviceProperties[$name];

                continue;
            }

            if (in_array($name, $serviceMethods)) {
                $parameters[$name] = [$this->service, $name]();

                continue;
            }

            if ($functionParameter->isDefaultValueAvailable()) {
                $parameters[$name] = $functionParameter->getDefaultValue();

                continue;
            }

            if ($functionParameter->allowsNull()) {
                $parameters[$name] = null;

                continue;
            }

            if ($functionParameter->isVariadic()) {
                $parameters[$name] = array_filter(
                    $parameters,
                    function (string $parameter) use ($parameters): bool {
                        return !key_exists($parameter, $parameters);
                    },
                    ARRAY_FILTER_USE_KEY
                );
                continue;
            }

            throw new DependencyInjectionException('%s is not a valid injectable parameter', $name);
        }

        return $parameters;
    }
}
