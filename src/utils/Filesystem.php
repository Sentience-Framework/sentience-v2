<?php

namespace src\utils;

use src\exceptions\FilesystemException;

class Filesystem
{
    public static function path(string $dir, ?string ...$components): string
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

        sort($items);

        $paths = [];

        foreach ($items as $item) {
            if (is_file($item)) {
                $paths[] = $item;
            }

            if (is_dir($item)) {
                $paths[] = $item;

                if ($depth == 0) {
                    continue;
                }

                array_push($paths, ...static::scandir($item, $depth - 1));
            }
        }

        return array_values($paths);
    }
}
