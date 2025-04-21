<?php

namespace src\sentience;

class Stdio
{
    public static function print(string $input)
    {
        file_put_contents('php://stdout', $input);
    }

    public static function printLn(string $input)
    {
        static::print($input . PHP_EOL);
    }

    public static function printF(string $input, ...$values)
    {
        static::print(sprintf($input, ...$values));
    }

    public static function printFLn(string $input, ...$values)
    {
        static::printLn(sprintf($input, ...$values));
    }

    public static function error(string $input)
    {
        file_put_contents('php://stderr', $input);
    }

    public static function errorLn(string $input)
    {
        static::error($input . PHP_EOL);
    }

    public static function errorF(string $input, ...$values)
    {
        static::error(sprintf($input, ...$values));
    }

    public static function errorFLn(string $input, ...$values)
    {
        static::errorLn(sprintf($input, ...$values));
    }
}
