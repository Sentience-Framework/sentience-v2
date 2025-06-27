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

function escape_chars(string $string, array $chars, string $replacement = '\\\$0', string $pattern = '/(?<!\\\\)(?:\\\\\\\\)*%s/'): string
{
    foreach ($chars as $char) {
        $string = preg_replace(
            sprintf(
                $pattern,
                preg_quote($char, '/')
            ),
            $replacement,
            $string
        );
    }

    return $string;
}

function extract_chars(string $string, array $chars): string
{
    return preg_replace(
        sprintf(
            '/[^%s]/',
            preg_quote(
                implode(
                    '',
                    $chars
                ),
                '/'
            )
        ),
        '',
        $string
    );
}
