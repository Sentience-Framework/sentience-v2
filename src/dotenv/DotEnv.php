<?php

namespace src\dotenv;

use src\exceptions\DotEnvException;

class DotEnv
{
    public static function loadEnv(?callable $parseValue = null): void
    {
        $env = getenv();

        foreach ($env as $key => $value) {
            $_ENV[$key] = $parseValue ? $parseValue($value, $key) : $value;
        }
    }

    public static function loadFile(string $filepath, ?string $exampleFilepath = null, array $variables = []): void
    {
        $parsedVariables = static::parseFile($filepath, $exampleFilepath, $variables);

        foreach ($parsedVariables as $key => $value) {
            $_ENV[$key] = $value;
        }
    }

    public static function parseFile(string $filepath, ?string $exampleFilepath = null, array $variables = []): array
    {
        $rawVariables = static::parseFileRaw($filepath, $exampleFilepath);

        $parsedVariables = $variables;

        foreach ($rawVariables as $key => $value) {
            $parsedVariables[$key] = static::parseVariable($value, $parsedVariables);
        }

        return $parsedVariables;
    }

    public static function parseFileRaw(string $filepath, ?string $exampleFilepath = null): array
    {
        if (!file_exists($filepath)) {
            static::createFile($filepath, $exampleFilepath);
        }

        $fileContents = file_get_contents($filepath);

        return static::parseDotEnvString($fileContents);
    }

    protected static function createFile(string $filepath, ?string $exampleFilepath): void
    {
        $exampleFilepath ? copy($exampleFilepath, $filepath) : file_put_contents($filepath, '');
    }

    protected static function parseDotEnvString(string $string): array
    {
        $isMatch = preg_match_all(
            '/^(?!\#)\s*([a-zA-Z0-9_]+)\s*=\s*(?|(\'.*?\')|(\".*?\")|(\`{3}[\s\S]*?\`{3})|([^#\r\n]*))\s*(?=[\r\n]|$|\#)/m',
            $string,
            $matches
        );

        if (!$isMatch) {
            throw new DotEnvException('parsing error');
        }

        return array_combine($matches[1], $matches[2]);
    }

    protected static function parseVariable(string $value, array $parsedVariables): mixed
    {
        if (str_starts_with($value, '[')) {
            return static::parseArrayValue($value, $parsedVariables);
        }

        if (preg_match('/^[\"\'\`]/', $value)) {
            return static::parseQuotedValue($value, $parsedVariables);
        }

        if (preg_match('/^\-{1}?[0-9]+$/', $value)) {
            return static::parseIntValue($value);
        }

        if (is_numeric($value)) {
            return static::parseFloatValue($value);
        }

        if (preg_match('/^false|true$/', $value)) {
            return static::parseBoolValue($value);
        }

        if ($value == 'null') {
            return static::parseNullValue($value);
        }

        return rtrim($value);
    }

    protected static function parseArrayValue(string $value, array $parsedVariables): array
    {
        $values = [];

        $isMatch = preg_match_all(
            '/(?:\[(?:[^\[\]]++|(?R))*\])|(?:\`{3}.*?\`{3})|(?:\"(?:\\\\.|[^\"\\\\])*\")|(?:\'(?:\\\\.|[^\'\\\\])*\')|(?:[^,W\s\[\]]+)/',
            substr($value, 1, -1),
            $matches
        );

        if (!$isMatch) {
            return $values;
        }

        return array_map(
            function (string $value) use ($parsedVariables): mixed {
                return static::parseVariable($value, $parsedVariables);
            },
            $matches[0]
        );
    }

    protected static function parseQuotedValue(string $value, array $parsedVariables): string
    {
        return match (substr($value, 0, 1)) {
            '"' => static::parseTemplateValue($value, '"', $parsedVariables),
            "'" => static::parseStringValue($value, "'"),
            '`' => static::parseTemplateValue($value, '```', $parsedVariables)
        };
    }

    protected static function parseTemplateValue(string $value, string $quote, array $parsedVariables): string
    {
        $string = static::parseStringValue($value, $quote);

        return preg_replace_callback(
            '/\$\{(.[^\}]*)\}/',
            function (array $matches) use ($parsedVariables): mixed {
                [$original, $key] = $matches;

                if (key_exists($key, $parsedVariables)) {
                    return $parsedVariables[$key];
                }

                return $original;
            },
            $string
        );
    }

    protected static function parseStringValue(string $value, string $quote): string
    {
        $quoteLength = strlen($quote);

        $valueWithoutQuotes = trim(
            substr(
                $value,
                $quoteLength,
                $quoteLength * -1
            ),
            "\r\n"
        );

        $quoteChar = substr($quote, 0, 1);

        return str_replace(
            sprintf('\\%s', $quoteChar),
            $quoteChar,
            $valueWithoutQuotes
        );
    }

    protected static function parseFloatValue(string $value): float
    {
        return (float) $value;
    }

    protected static function parseIntValue(string $value): int
    {
        return (int) $value;
    }

    protected static function parseBoolValue(string $value): bool
    {
        return match ($value) {
            'false' => false,
            'true' => true
        };
    }

    protected static function parseNullValue(string $value): mixed
    {
        return null;
    }
}
