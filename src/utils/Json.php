<?php

namespace src\utils;

use src\exceptions\JsonException;

class Json
{
    public static function encode(mixed $value, int $flags = 0, int $depth = 512): string
    {
        $json = json_encode($value, $flags, $depth);

        $error = json_last_error_msg();

        if ($error == 'No error') {
            return $json;
        }

        throw new JsonException($error);
    }

    public static function decode(string $json, bool $associative = true, int $depth = 512, int $flags = 0): mixed
    {
        $value = json_decode($json, $associative, $depth, $flags);

        $error = json_last_error_msg();

        if ($error == 'No error') {
            return $value;
        }

        throw new JsonException($error);
    }
}
