<?php

namespace src\controllers;

use src\sentience\Stdio;
use src\utils\Filesystem;
use src\utils\Terminal;

class DevToolsController extends Controller
{
    public function sortImports(): void
    {
        $terminalWidth = Terminal::getWidth();

        $equalSigns = ($terminalWidth - 17) / 2 - 1;

        Stdio::errorFLn(
            '%s Development tools %s',
            str_repeat('=', ceil($equalSigns)),
            str_repeat('=', floor($equalSigns))
        );

        $files = Filesystem::scandir(SENTIENCE_DIR, -1);

        $importRegex = '/^use\s+[^;]+;/m';

        foreach ($files as $file) {
            if (is_dir($file)) {
                continue;
            }

            if (str_starts_with($file, Filesystem::path(SENTIENCE_DIR, 'vendor'))) {
                continue;
            }

            if (!str_ends_with($file, '.php')) {
                continue;
            }

            $fileContents = file_get_contents($file);

            $isMatch = preg_match_all($importRegex, $fileContents, $matches);

            if (!$isMatch) {
                continue;
            }

            $imports = $matches[0];

            $globalImports = [];
            $namespaceImports = [];

            foreach ($imports as $import) {
                if (str_contains($import, '\\')) {
                    $namespaceImports[] = $import;

                    continue;
                }

                $globalImports[] = $import;
            }

            natcasesort($globalImports);
            natcasesort($namespaceImports);

            $sortedImports = [...$globalImports, ...$namespaceImports];

            $index = 0;

            file_put_contents(
                $file,
                preg_replace_callback(
                    $importRegex,
                    function () use (&$index, $sortedImports): string {
                        $import = $sortedImports[$index];

                        $index++;

                        return $import;
                    },
                    $fileContents
                )
            );

            Stdio::printFLn('Sorted %d imports in: %s', count($sortedImports), $file);
        }

        Stdio::printLn(str_repeat('=', $terminalWidth));
    }
}
