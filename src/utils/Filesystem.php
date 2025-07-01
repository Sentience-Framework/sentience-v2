<?php

namespace src\utils;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
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

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $path,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $paths = [];

        foreach ($iterator as $splFileInfo) {
            if ($iterator->getDepth() > $depth) {
                continue;
            }

            $paths[] = $splFileInfo->getPathname();
        }

        natcasesort($paths);

        return array_values($paths);
    }
}
