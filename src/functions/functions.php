<?php

function env(string $key, mixed $default = null): mixed
{
    if (!key_exists($key, $_ENV)) {
        return $default;
    }

    return $_ENV[$key];
}

function is_cli(): bool
{
    return empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0;
}

function path(string $dir, ?string ...$components): string
{
    if (!$components) {
        return $dir;
    }

    $chars = implode('', ['/', '\\']);

    $dir = rtrim($dir, $chars);

    $components = array_filter(
        array_map(
            function (?string $component) use ($chars): ?string {
                if (!$component) {
                    return null;
                }

                return trim($component, $chars);
            },
            $components
        )
    );

    return implode(
        DIRECTORY_SEPARATOR,
        [
            $dir,
            ...$components
        ]
    );
}

function to_snake_case(string $string): string
{
    $string = preg_replace('/([a-z])([A-Z])/', '$1 $2', $string);
    $string = preg_replace('/[_\-\s]+/', '_', strtolower($string));

    return $string;
}

function to_camel_case($string): string
{
    $string = lcfirst(to_pascal_case($string));

    return $string;
}

function to_pascal_case($string): string
{
    $string = preg_replace('/[-_\s]+/', ' ', $string);
    $string = ucwords($string);
    $string = str_replace(' ', '', $string);

    return $string;
}

function escape_chars(string $string, array $chars, string $replacement = '\\\$0'): string
{
    foreach ($chars as $char) {
        $char = !preg_match('/[a-zA-Z0-9]/', $char)
            ? sprintf('\\%s', $char)
            : $char;

        $string = preg_replace(
            sprintf('/%s/', $char),
            $replacement,
            $string
        );
    }

    return $string;
}
