<?php

namespace src\controllers;

use Throwable;
use src\database\Database;
use src\database\queries\Query;
use src\dotenv\DotEnv;
use src\exceptions\BuiltInWebServerException;
use src\exceptions\MigrationException;
use src\exceptions\TerminalException;
use src\migrations\MigrationFactory;
use src\models\Migration;
use src\sentience\Stdio;
use src\utils\Filesystem;
use src\utils\Reflector;
use src\utils\Terminal;

class SentienceController extends Controller
{
    public function startServer(): void
    {
        $terminalWidth = Terminal::getWidth();

        if ($terminalWidth < 40) {
            throw new TerminalException('terminal width of %d is too small. minimum width of 40 required', $terminalWidth);
        }

        $dir = escapeshellarg(Filesystem::path(SENTIENCE_DIR, 'public'));
        $bin = escapeshellarg(defined(PHP_BINARY) ? PHP_BINARY : 'php');
        $host = env('SERVER_HOST', 'localhost');
        $port = env('SERVER_PORT', 8000);

        $command = sprintf('cd %s && %s -S %s:%d', $dir, $bin, $host, $port);

        if (PHP_OS_FAMILY == 'Windows') {
            passthru($command);

            return;
        }

        Terminal::stream(
            $command,
            function ($stdout, $stderr) use ($terminalWidth, &$startTime, &$endTime, &$path): void {
                if (empty($stderr)) {
                    return;
                }

                $stderr = str_ends_with($stderr, PHP_EOL)
                    ? substr($stderr, 0, -1)
                    : $stderr;

                $lines = explode(PHP_EOL, $stderr);

                foreach ($lines as $line) {
                    if (preg_match('/\(reason:\s*(.*?)\)/', $line, $matches)) {
                        throw new BuiltInWebServerException($matches[1]);
                    }

                    if (preg_match('/^\[.*?\]\sPHP/', $line)) {
                        $equalSigns = ($terminalWidth - 28) / 2 - 1;

                        Stdio::printFLn(
                            '%s Sentience development server %s',
                            str_repeat('=', ceil($equalSigns)),
                            str_repeat('=', floor($equalSigns))
                        );

                        continue;
                    }

                    if (preg_match('/^\[.*?\]\s.*\:\d+\s(\w+)/', $line, $matches)) {
                        $status = $matches[1];

                        if ($status == 'Accepted') {
                            $startTime = microtime(true);

                            continue;
                        }

                        if ($status == 'Closing') {
                            $endTime = microtime(true);

                            Stdio::errorFLn(
                                '%s (%.2f ms) %s',
                                date('Y-m-d H:i:s'),
                                ($endTime - $startTime) * 1000,
                                $path
                            );

                            continue;
                        }

                        continue;
                    }

                    if (preg_match('/^\[.*?\]\s.*\:\d+\s\[\d+\]\:\s\w+\s(.*)/', $line, $matches)) {
                        $path = $matches[1];

                        continue;
                    }

                    Stdio::errorLn($line);
                }

                return;
            },
            0
        );
    }

    public function initMigrations(Database $database): void
    {
        $migration = new Migration($database);

        $migration->createTable(true);

        Stdio::printLn('Migrations table created');
    }

    public function applyMigrations(Database $database): void
    {
        $migrationsDir = Filesystem::path(SENTIENCE_DIR, 'migrations');

        $migrations = array_filter(
            Filesystem::scandir($migrationsDir),
            function (string $path): bool {
                if (!str_ends_with(strtolower($path), '.php')) {
                    return false;
                }

                if (!preg_match('/[0-9]{14}[\-\_][a-zA-Z0-9\-\_]+\.php$/', $path)) {
                    return false;
                }

                return true;
            }
        );

        if (count($migrations) == 0) {
            Stdio::printLn('No migrations found');

            return;
        }

        $highestBatch = $database->select()
            ->table(Migration::getTable())
            ->columns([
                Query::alias(
                    Query::raw('MAX(batch)'),
                    'batch'
                )
            ])
            ->execute()
            ->fetch()
            ->batch ?? 0;

        $nextBatch = $highestBatch + 1;

        foreach ($migrations as $filepath) {
            $filename = basename($filepath);

            $alreadyApplied = $database->select()
                ->table(Migration::getTable())
                ->whereEquals('filename', $filename)
                ->exists();

            if ($alreadyApplied) {
                Stdio::printFLn('Migration %s already applied', $filename);

                continue;
            }

            $migration = include $filepath;

            $database->transactionInCallback(function (Database $database) use ($migration): void {
                $migration->apply($database);
            });

            $migrationModel = new Migration($database);
            $migrationModel->batch = $nextBatch;
            $migrationModel->filename = $filename;
            $migrationModel->appliedAt = Query::now();
            $migrationModel->insert();

            Stdio::printFLn('Migration %s applied', $filename);
        }
    }

    public function rollbackMigrations(Database $database): void
    {
        $migrationsDir = Filesystem::path(SENTIENCE_DIR, 'migrations');

        $migrations = array_filter(
            Filesystem::scandir($migrationsDir),
            function (string $path): bool {
                if (!str_ends_with(strtolower($path), '.php')) {
                    return false;
                }

                if (!preg_match('/[0-9]{14}[\-\_][a-zA-Z0-9\-\_]+\.php$/', $path)) {
                    return false;
                }

                return true;
            }
        );

        if (count($migrations) == 0) {
            Stdio::printLn('No migrations found');

            return;
        }

        $highestBatch = $database->select()
            ->table(Migration::getTable())
            ->columns([
                Query::alias(
                    Query::raw('MAX(batch)'),
                    'batch'
                )
            ])
            ->execute()
            ->fetch()
            ->batch ?? 0;

        if ($highestBatch == 0) {
            Stdio::printLn('No migrations found to rollback');

            return;
        }

        $migrationsToRevert = $database->select()
            ->table(Migration::getTable())
            ->whereEquals('batch', $highestBatch)
            ->orderByDesc('applied_at')
            ->execute()
            ->fetchAll();

        foreach ($migrationsToRevert as $migrationToRevert) {
            $filename = $migrationToRevert->filename;
            $filepath = Filesystem::path($migrationsDir, $filename);

            if (!file_exists($filepath)) {
                throw new MigrationException('unable to find migration %s', $filename);
            }

            $migration = include $filepath;

            $database->transactionInCallback(function (Database $database) use ($migration): void {
                $migration->rollback($database);
            });

            $database->delete()
                ->table(Migration::getTable())
                ->whereEquals('id', $migrationToRevert->id)
                ->execute();

            Stdio::printFLn('Migration %s rolled back', $filename);
        }
    }

    public function createMigration(array $words, array $flags): void
    {
        $name = $flags['name'] ?? $words[0] ?? null;

        if (is_null($name)) {
            Stdio::errorLn('Please provide a name for the migration');

            return;
        }

        $timestamp = date('YmdHis');

        $migrationFilename = sprintf('%s_%s.php', $timestamp, $name);

        $migrationFilepath = Filesystem::path(
            SENTIENCE_DIR,
            'migrations',
            $migrationFilename
        );

        $migrationFileContents = MigrationFactory::create();

        file_put_contents($migrationFilepath, $migrationFileContents);

        Stdio::printFLn('Migration %s created successfully', $migrationFilename);
    }

    public function initModel(Database $database, array $words, array $flags): void
    {
        $class = $flags['model'] ?? $words[0] ?? null;

        if (!$class) {
            Stdio::errorLn('No model set');

            return;
        }

        $class = !str_contains('\\', $class)
            ? $class = sprintf('\\src\\models\\%s', $class)
            : $class;

        if (!class_exists($class)) {
            Stdio::errorFLn('Model %s does not exist', $class);

            return;
        }

        $model = new $class($database);

        $migrationName = sprintf(
            '%s_create_%s_table.php',
            date('YmdHis'),
            $model::getTable()
        );

        $migrationFileContents = MigrationFactory::create(
            [
                sprintf('$model = new %s($database);', $class),
                '$model->createTable(true);'
            ],
            [
                sprintf('$model = new %s($database);', $class),
                '$model->dropTable(true);'
            ]
        );

        $migrationFilepath = Filesystem::path(SENTIENCE_DIR, 'migrations', $migrationName);

        file_put_contents($migrationFilepath, $migrationFileContents);

        $migration = include $migrationFilepath;

        try {
            $database->transactionInCallback(function (Database $database) use ($migration): void {
                $migration->apply($database);
            });
        } catch (Throwable $exception) {
            unlink($migrationFilepath);

            throw $exception;
        }

        $highestBatch = $database->select()
            ->table(Migration::getTable())
            ->columns([
                Query::alias(
                    Query::raw('MAX(batch)'),
                    'batch'
                )
            ])
            ->execute()
            ->fetch()
            ->batch ?? 0;

        $nextBatch = $highestBatch + 1;

        $migrationModel = new Migration($database);
        $migrationModel->batch = $nextBatch;
        $migrationModel->filename = $migrationName;
        $migrationModel->appliedAt = Query::now();
        $migrationModel->insert();

        Stdio::printFLn('Migration for model %s created successfully', Reflector::getShortName($model));
    }

    public function updateModel(Database $database, array $words, array $flags): void
    {
        $class = $flags['model'] ?? $words[0] ?? null;

        if (!$class) {
            Stdio::errorLn('No model set');

            return;
        }

        $class = !str_contains('\\', $class)
            ? $class = sprintf('\\src\\models\\%s', $class)
            : $class;

        if (!class_exists($class)) {
            Stdio::errorFLn('Model %s does not exist', $class);

            return;
        }

        $model = new $class($database);

        $migrationName = sprintf(
            '%s_alter_%s_table.php',
            date('YmdHis'),
            $model::getTable()
        );

        $migrationFileContents = MigrationFactory::create(
            [
                sprintf('$model = new %s($database);', $class),
                '$model->alterTable();'
            ],
            [
                sprintf('$model = new %s($database);', $class),
                '$model->alterTable();'
            ]
        );

        $migrationFilepath = Filesystem::path(SENTIENCE_DIR, 'migrations', $migrationName);

        file_put_contents($migrationFilepath, $migrationFileContents);

        $migration = include $migrationFilepath;

        try {
            $database->transactionInCallback(function (Database $database) use ($migration): void {
                $migration->apply($database);
            });
        } catch (Throwable $exception) {
            unlink($migrationFilepath);

            throw $exception;
        }

        $highestBatch = $database->select()
            ->table(Migration::getTable())
            ->columns([
                Query::alias(
                    Query::raw('MAX(batch)'),
                    'batch'
                )
            ])
            ->execute()
            ->fetch()
            ->batch ?? 0;

        $nextBatch = $highestBatch + 1;

        $migrationModel = new Migration($database);
        $migrationModel->batch = $nextBatch;
        $migrationModel->filename = $migrationName;
        $migrationModel->appliedAt = Query::now();
        $migrationModel->insert();

        Stdio::printFLn('Migration for model %s created successfully', Reflector::getShortName($model));
    }

    public function resetModel(Database $database, array $words, array $flags): void
    {
        $class = $flags['model'] ?? $words[0] ?? null;

        if (!$class) {
            Stdio::errorLn('No model set');

            return;
        }

        $class = !str_contains('\\', $class)
            ? $class = sprintf('\\src\\models\\%s', $class)
            : $class;

        if (!class_exists($class)) {
            Stdio::errorFLn('Model %s does not exist', $class);

            return;
        }

        $model = new $class($database);

        $migrationName = sprintf(
            '%s_reset_%s_table.php',
            date('YmdHis'),
            $model::getTable()
        );

        $migrationFileContents = MigrationFactory::create(
            [
                sprintf('$model = new %s($database);', $class),
                '$model->dropTable(true);',
                '$model->createTable(true);'
            ]
        );

        $migrationFilepath = Filesystem::path(SENTIENCE_DIR, 'migrations', $migrationName);

        file_put_contents($migrationFilepath, $migrationFileContents);

        $migration = include $migrationFilepath;

        try {
            $database->transactionInCallback(function (Database $database) use ($migration): void {
                $migration->apply($database);
            });
        } catch (Throwable $exception) {
            unlink($migrationFilepath);

            throw $exception;
        }

        $highestBatch = $database->select()
            ->table(Migration::getTable())
            ->columns([
                Query::alias(
                    Query::raw('MAX(batch)'),
                    'batch'
                )
            ])
            ->execute()
            ->fetch()
            ->batch ?? 0;

        $nextBatch = $highestBatch + 1;

        $migrationModel = new Migration($database);
        $migrationModel->batch = $nextBatch;
        $migrationModel->filename = $migrationName;
        $migrationModel->appliedAt = Query::now();
        $migrationModel->insert();

        Stdio::printFLn('Migration for model %s created successfully', Reflector::getShortName($model));
    }

    public function fixDotEnv(array $words, array $flags): void
    {
        $dotEnv = $flags['dot-env'] ?? $words[0] ?? '.env';
        $dotEnvExample = $flags['dot-env-example'] ?? $words[1] ?? '.env.example';

        $dotEnvFilepath = Filesystem::path(SENTIENCE_DIR, $dotEnv);
        $dotEnvExampleFilepath = Filesystem::path(SENTIENCE_DIR, $dotEnvExample);

        $dotEnvVariables = DotEnv::parseFileRaw($dotEnvFilepath);
        $dotEnvExampleVariables = DotEnv::parseFileRaw($dotEnvExampleFilepath);

        $missingVariables = [];

        foreach ($dotEnvExampleVariables as $key => $value) {
            if (array_key_exists($key, $dotEnvVariables)) {
                continue;
            }

            $missingVariables[$key] = $value;
        }

        if (count($missingVariables) == 0) {
            Stdio::printFLn(
                '%s is up to date',
                $dotEnv
            );

            return;
        }

        $dotEnvFileContents = file_get_contents($dotEnvFilepath);

        $lines = preg_split('/[\r\n|\n|\r]/', $dotEnvFileContents);

        if (!empty(end($lines))) {
            $lines[] = '';
        }

        $lines[] = sprintf(
            '# imported %s variables from %s on %s',
            count($missingVariables),
            $dotEnvExample,
            date('Y-m-d H:i:s')
        );

        foreach ($missingVariables as $key => $value) {
            $lines[] = sprintf(
                '%s=%s',
                $key,
                $value
            );
        }

        $lines[] = '';

        $modifiedDotEnvFileContents = implode(PHP_EOL, $lines);

        file_put_contents($dotEnvFilepath, $modifiedDotEnvFileContents);

        Stdio::printFLn('Added %d variables to %s', count($missingVariables), $dotEnv);
    }
}
