<?php

namespace src\utils;

use src\exceptions\FilesystemException;

class Filesystem
{
    public const WINDOWS_DIRECTORY_SEPARATOR = '\\';
    public const UNIX_DIRECTORY_SEPARATOR = '/';

    public static function path(string $dir, ?string ...$components): string
    {
        if (!$components) {
            return $dir;
        }

        $chars = static::WINDOWS_DIRECTORY_SEPARATOR . static::UNIX_DIRECTORY_SEPARATOR;

        $dir = preg_match('/^\/|\\\\\\\\$/', $dir)
            ? rtrim($dir, $chars)
            : $dir;

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

    public static function scandir(string $path, int $depth = 0): array
    {
        if (!file_exists($path)) {
            throw new FilesystemException('directory %s does not exist', $path);
        }

        $items = scandir($path);

        if (is_bool($items)) {
            return [];
        }

        $items = array_filter(
            $items,
            function (string $item): bool {
                return !in_array($item, ['.', '..']);
            }
        );

        if (count($items) == 0) {
            return [];
        }

        $items = array_map(
            function (string $item) use ($path): string {
                return static::path($path, $item);
            },
            $items
        );

        natcasesort($items);

        $paths = [];

        foreach ($items as $item) {
            $paths[] = $item;

            if (is_file($item)) {
                continue;
            }

            if ($depth == 0) {
                continue;
            }

            array_push($paths, ...static::scandir($item, $depth - 1));
        }

        return array_values($paths);
    }
}
