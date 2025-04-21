<?php

namespace src\migrations;

class MigrationFactory
{
    public static function createMigration(array $up = [], array $down = []): string
    {
        $lines = [];

        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'use src\database\Database;';
        $lines[] = 'use src\migrations\MigrationInterface;';
        $lines[] = '';
        $lines[] = 'return new class implements MigrationInterface {';
        $lines[] = '    public function up(Database $database): void';
        $lines[] = '    {';

        foreach ($up as $line) {
            $lines[] = sprintf('        %s', $line);
        }

        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    public function down(Database $database): void';
        $lines[] = '    {';

        foreach ($down as $line) {
            $lines[] = sprintf('        %s', $line);
        }

        $lines[] = '    }';
        $lines[] = '};';

        return implode(PHP_EOL, $lines);
    }
}
