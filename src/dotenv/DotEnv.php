<?php

namespace src\dotenv;

class DotEnv
{
    public static function loadEnv(bool $parseBooleans = false, bool $parseDirectoryArrays = false): void
    {
        $env = getenv();

        foreach ($env as $key => $value) {
            if ($parseBooleans && in_array($value, ['0', '1'])) {
                $_ENV[$key] = [
                    '0' => false,
                    '1' => true
                ][$value];

                continue;
            }

            if ($parseDirectoryArrays && str_contains($value, DIRECTORY_SEPARATOR) && str_contains($value, PATH_SEPARATOR)) {
                $_ENV[$key] = explode(':', $value);

                continue;
            }

            $_ENV[$key] = $value;
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
        (bool) $exampleFilepath
            ? copy($exampleFilepath, $filepath)
            : file_put_contents($filepath, '');
    }

    protected static function parseDotEnvString(string $string): array
    {
        $lines = preg_split('/(\r\n|\n|\r)/', $string) ?? [$string];

        $variables = [];

        foreach ($lines as $line) {
            $isMatch = preg_match('/(?:^|^)\s*(?:export\s+)?([\w.-]+)(?:\s*=\s*?|:\s+?)(\s*\'(?:\\\'|[^\'])*\'|\s*"(?:(?:\\")|[^"])*"|`(?:\\`|[^`])*`|[^#\r\n]+)?\s*(?:#.*)?(?:$|$)/', $line, $matches);

            if (!$isMatch) {
                continue;
            }

            if (count($matches) < 3) {
                continue;
            }

            $key = trim($matches[1]);
            $value = trim($matches[2]);

            $variables[$key] = $value;
        }

        return $variables;
    }

    protected static function parseVariable(string $value, array $parsedVariables): mixed
    {
        $trimmedValue = trim($value);

        if (substr($trimmedValue, 0, 1) == '[') {
            return static::parseArrayValue($trimmedValue, $parsedVariables);
        }

        if (substr($trimmedValue, 0, 1) == '"') {
            return static::parseTemplateValue($trimmedValue, $parsedVariables);
        }

        if (substr($trimmedValue, 0, 1) == "'") {
            return static::parseStringValue($trimmedValue, "'");
        }

        if (str_contains($trimmedValue, '.')) {
            return static::parseFloatValue($trimmedValue);
        }

        if (preg_match('/.*[0-9].*/', $trimmedValue)) {
            return static::parseIntValue($trimmedValue);
        }

        if (in_array(strtolower($trimmedValue), ['true', 'false'])) {
            return static::parseBoolValue($trimmedValue);
        }

        if ($trimmedValue == 'null') {
            return static::parseNullValue($trimmedValue);
        }

        return null;
    }

    protected static function parseArrayValue(string $value, array $parsedVariables): array
    {
        $values = [];

        $isMatch = preg_match_all('/(\"(.*?)\")|(\'(.*?)\')|[-\w.]+/', $value, $matches, PREG_UNMATCHED_AS_NULL);

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

    protected static function parseTemplateValue(string $value, array $parsedVariables): string
    {
        $string = static::parseStringValue($value, '"');

        $envTemplateRegex = '/\$\{(.[^\}]*)\}/';

        $isMatch = preg_match_all($envTemplateRegex, $string, $matches);

        if (!$isMatch) {
            return $string;
        }

        return preg_replace_callback(
            $envTemplateRegex,
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
        $quoteTrim = substr($value, 1, -1);

        return str_replace(
            sprintf('\\%s', $quote),
            $quote,
            $quoteTrim
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
        return match (strtolower($value)) {
            'true' => true,
            'false' => false
        };
    }

    protected static function parseNullValue(string $value): mixed
    {
        return null;
    }
}
