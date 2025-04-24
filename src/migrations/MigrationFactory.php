<?php

namespace src\migrations;

class MigrationFactory
{
    public static function createMigration(array $apply = [], array $rollback = []): string
    {
        $lines = [];

        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'use src\database\Database;';
        $lines[] = 'use src\migrations\MigrationInterface;';
        $lines[] = '';
        $lines[] = 'return new class implements MigrationInterface {';
        $lines[] = '    public function apply(Database $database): void';
        $lines[] = '    {';

        foreach ($apply as $line) {
            $lines[] = sprintf('        %s', $line);
        }

        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public function rollback(Database $database): void';
        $lines[] = '    {';

        foreach ($rollback as $line) {
            $lines[] = sprintf('        %s', $line);
        }

        $lines[] = '    }';
        $lines[] = '};';
        $lines[] = '';

        return implode(PHP_EOL, $lines);
    }
}
