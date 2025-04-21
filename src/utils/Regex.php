<?php

namespace src\utils;

class Regex
{
    public static function template(string $template, string $subject, string $pattern = '.*'): ?array
    {
        $keys = [];

        $regex = preg_replace_callback(
            '/{(.[^\}]*)}/',
            function (array $matches) use (&$keys, $pattern): string {
                $keys[] = $matches[1];

                return sprintf('(%s)', $pattern);
            },
            sprintf(
                '/^%s$/',
                escape_chars($template, ['.', '/', '+', '*', '?', '^', '[', ']', '$', '(', ')', '=', '!', '<', '>', '|', ':', '-'])
            )
        );

        $isMatch = preg_match($regex, $subject, $matches);

        if (!$isMatch) {
            return null;
        }

        $values = array_splice($matches, 1);

        return array_combine($keys, $values);
    }
}
