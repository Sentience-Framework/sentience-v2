<?php

namespace src\utils;

class UrlEncoding
{
    public static function encode(array $associative): string
    {
        if (empty($associative)) {
            return '';
        }

        $encoded = [];

        foreach ($associative as $key => $value) {
            if (is_array($value)) {
                array_walk(
                    $value,
                    function (mixed $value) use ($key, &$encoded): void {
                        $encoded[] = sprintf(
                            '%s=%s',
                            urlencode($key),
                            urlencode($value)
                        );
                    }
                );

                continue;
            }

            $encoded[] = sprintf(
                '%s=%s',
                urlencode($key),
                urlencode($value)
            );
        }

        return implode('&', $encoded);
    }

    public static function decode(string $string, bool $unique = true): array
    {
        $isMatch = preg_match_all('/([^&=]+)(=([^&]*))?/', $string, $matches);

        if (!$isMatch) {
            return [];
        }

        $urlEncodedKeys = $matches[1];
        $urlEncodedValues = $matches[3];

        $decoded = [];

        foreach ($urlEncodedKeys as $index => $urlEncodedKey) {
            $urlEncodedValue = $urlEncodedValues[$index];

            $key = urldecode($urlEncodedKey);
            $value = urldecode($urlEncodedValue);

            if (!key_exists($key, $decoded)) {
                $decoded[$key] = [];
            }

            $decoded[$key][] = $value;
        }

        if ($unique) {
            return array_map(
                function (array $values): mixed {
                    return end($values);
                },
                $decoded
            );
        }

        return array_map(
            function (array $values): mixed {
                if (count($values) == 1) {
                    return end($values);
                }

                return $values;
            },
            $decoded
        );
    }
}
