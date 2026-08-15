<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\Service\Database\Dumper;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class MySqlDatabaseDumper implements DatabaseDumperInterface
{
    /**
     * @param string $name The app-level database name (e.g. "mysql")
     * @param string $database The actual database name to dump/restore
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string|null $socket
     * @param int|null $port
     * @param null|string $binary Optional override for mysqldump/mysql binary
     */
    public function __construct(
        protected string $name,
        protected string $database,
        protected string $host,
        #[\SensitiveParameter] protected string $user,
        #[\SensitiveParameter] protected string $password,
        protected null|string $socket = null,
        protected null|int $port = null,
        protected null|string $binary = null,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return $this->name;
    }
    
    /**
     * Returns the file extension used for dump files produced by this dumper.
     *
     * The extension MUST NOT include a leading dot.
     * Examples: "sql", "sqlite", "json", "bson"
     *
     * @return string
     */
    public function fileExtension(): string
    {
        return 'sql';
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $tables, $destination, null|float $timeoutSeconds = null): void
    {
        if (!is_resource($destination)) {
            throw new DumpException('Destination must be a writable stream resource.');
        }

        if (empty($tables)) {
            throw new DumpException('No tables specified for dump.');
        }

        $cmd = $this->buildDumpCommand($tables);

        $process = new Process(
            command: $cmd,
            env: ['MYSQL_PWD' => $this->password],
        );
        $process->setTimeout($timeoutSeconds);

        // Important: we stream stdout directly to $destination and never call getOutput(),
        // to avoid buffering multi-GB dumps in memory.
        try {
            $process->run(function ($type, $buffer) use ($destination) {
                fwrite($destination, $buffer);
            });
        } catch (ProcessTimedOutException $e) {
            throw new DumpTimedOutException('Dump process timed out.', 0, $e);
        }

        if (!$process->isSuccessful()) {
            throw new DumpException(
                "mysqldump failed with exit code {$process->getExitCode()}. Error: {$process->getErrorOutput()}"
            );
        }
    }

    public function restore($source, null|float $timeoutSeconds = null): void
    {
        if (!is_resource($source)) {
            throw new RestoreException('Source must be a readable stream resource.');
        }

        $cmd = $this->buildRestoreCommand();

        $process = new Process(
            command: $cmd,
            env: ['MYSQL_PWD' => $this->password],
        );
        $process->setInput($source);
        $process->setTimeout($timeoutSeconds);

        try {
            $process->run();
        } catch (ProcessTimedOutException $e) {
            throw new RestoreTimedOutException('Restore process timed out.', 0, $e);
        }

        if (!$process->isSuccessful()) {
            throw new RestoreException(
                "mysql restore failed with exit code {$process->getExitCode()}. Error: {$process->getErrorOutput()}"
            );
        }
    }

    /**
     * @param array<string> $tables
     * @return array<int, string>
     */
    private function buildDumpCommand(array $tables): array
    {
        $bin = $this->resolveBinary(
            default: 'mysqldump',
            windowsPaths: $this->commonWindowsDumpPaths(),
        );

        $cmd = [
            $bin,
            '--skip-lock-tables',
            '--single-transaction',
            '--quick',
            '--default-character-set=utf8mb4',
            '-h', $this->host,
            '-u', $this->user,
            // password via MYSQL_PWD env
        ];

        if ($this->port !== null) {
            $cmd[] = '--port=' . $this->port;
        }

        if ($this->socket !== null) {
            $cmd[] = '--socket=' . $this->socket;
        }

        $cmd[] = $this->database;

        foreach ($tables as $table) {
            $cmd[] = $table;
        }

        return $cmd;
    }

    /**
     * @return array<int, string>
     */
    private function buildRestoreCommand(): array
    {
        $bin = $this->resolveBinary(
            default: 'mysql',
            windowsPaths: $this->commonWindowsMysqlPaths(),
        );

        $cmd = [
            $bin,
            '-h', $this->host,
            '-u', $this->user,
            // password via MYSQL_PWD env
            $this->database,
        ];

        if ($this->port !== null) {
            $cmd[] = '--port=' . $this->port;
        }

        if ($this->socket !== null) {
            $cmd[] = '--socket=' . $this->socket;
        }

        return $cmd;
    }

    private function resolveBinary(string $default, array $windowsPaths): string
    {
        if ($this->binary !== null) {
            return $this->binary;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            foreach ($windowsPaths as $path) {
                if (is_file($path)) {
                    return $path;
                }
            }
        }

        return $default;
    }

    private function commonWindowsDumpPaths(): array
    {
        return [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'C:\\Program Files\\MariaDB 10.6\\bin\\mysqldump.exe',
            'C:\\Program Files\\MariaDB 10.11\\bin\\mysqldump.exe',
        ];
    }

    private function commonWindowsMysqlPaths(): array
    {
        return [
            'C:\\xampp\\mysql\\bin\\mysql.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysql.exe',
            'C:\\Program Files\\MariaDB 10.6\\bin\\mysql.exe',
            'C:\\Program Files\\MariaDB 10.11\\bin\\mysql.exe',
        ];
    }
}