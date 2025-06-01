<?php

namespace src\utils;

use ReflectionClass;
use ReflectionProperty;

class Reflector
{
    public static function getShortName(string|object $objectOrClass): string
    {
        $reflectionClass = new ReflectionClass($objectOrClass);

        return $reflectionClass->getShortName();
    }

    public static function isPropertyInitialized(object $class, string $property): bool
    {
        $reflectionClass = new ReflectionProperty($class, $property);

        return $reflectionClass->isInitialized($class);
    }

    public static function hasDefaultValue(string|object $objectOrClass, string $property): bool
    {
        $reflectionProperty = new ReflectionProperty($objectOrClass, $property);

        return $reflectionProperty->hasDefaultValue();
    }

    public static function getDefaultValue(string|object $objectOrClass, string $property): mixed
    {
        $reflectionProperty = new ReflectionProperty($objectOrClass, $property);

        if (!$reflectionProperty->hasDefaultValue()) {
            return null;
        }

        return $reflectionProperty->getDefaultValue();
    }

    public static function isSubclassOf(string|object $objectOrClass, string $parent): mixed
    {
        $reflectionClass = new ReflectionClass($objectOrClass);

        return $reflectionClass->isSubclassOf($parent);
    }
}
