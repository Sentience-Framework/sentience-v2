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
    return php_sapi_name() == 'cli';
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
