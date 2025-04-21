<?php

namespace src\utils;

use src\exceptions\FilesystemException;

class Filesystem
{
    public static function scandir(string $path, int $depth = 0): array
    {
        if (!file_exists($path)) {
            throw new FilesystemException('directory %s does not exist', $path);
        }

        $items = array_map(
            function (string $item) use ($path): string {
                return file_path($path, $item);
            },
            array_filter(
                scandir($path),
                function (string $item): bool {
                    return !in_array($item, ['.', '..']);
                }
            )
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
