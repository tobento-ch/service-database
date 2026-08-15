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

/**
 * Provides streaming-based dump and restore operations for a single database.
 *
 * Dump operations write SQL to a caller-provided writable stream.
 * Restore operations read SQL from a caller-provided readable stream.
 *
 * Implementations must support large, streaming workloads and may enforce
 * optional timeouts to bound execution time.
 */
interface DatabaseDumperInterface
{
    /**
     * Returns the database name this handler is responsible for.
     *
     * @return string
     */
    public function name(): string;
    
    /**
     * Returns the file extension used for dump files produced by this dumper.
     *
     * The extension MUST NOT include a leading dot.
     * Examples: "sql", "sqlite", "json", "bson"
     *
     * @return string
     */
    public function fileExtension(): string;

    /**
     * Dumps selected tables into a writable destination stream.
     *
     * @param array<string> $tables
     * @param resource $destination Writable stream.
     * @param float|null $timeoutSeconds Optional timeout in seconds.
     * @return void
     * @throws DumpException If the dump could not be produced, including when the table list is empty.
     * @throws DumpTimedOutException If the dump times out.
     */
    public function dump(array $tables, $destination, null|float $timeoutSeconds = null): void;

    /**
     * Restores a database from a readable SQL source stream.
     *
     * @param resource $source Readable stream containing SQL.
     * @param float|null $timeoutSeconds Optional timeout in seconds.
     * @return void
     * @throws RestoreException If the restore fails.
     * @throws RestoreTimedOutException If the restore times out.
     *
     * Note: Partial restore failure may leave the database in an undefined state.
     * Callers should ensure a backup exists before restore.
     */
    public function restore($source, null|float $timeoutSeconds = null): void;
}