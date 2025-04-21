<?php

namespace src\utils;

use ReflectionClass;
use ReflectionProperty;

class Reflector
{
    public static function getShortName(string|object $objectOrClass): string
    {
        return (new ReflectionClass($objectOrClass))->getShortName();
    }

    public static function isPropertyInitialized(object $class, string $property): bool
    {
        return (new ReflectionProperty($class, $property))->isInitialized($class);
    }

    public static function hasDefaultValue(string|object $objectOrClass, string $property): bool
    {
        return (new ReflectionProperty($objectOrClass, $property))->hasDefaultValue();
    }

    public static function getDefaultValue(string|object $objectOrClass, string $property): mixed
    {
        $reflectionProperty = new ReflectionProperty($objectOrClass, $property);

        if (!$reflectionProperty->hasDefaultValue()) {
            return null;
        }

        return $reflectionProperty->getDefaultValue();
    }
}
