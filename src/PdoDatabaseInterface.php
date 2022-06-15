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

namespace Tobento\Service\Database;

use PDOStatement;
use PDO;
use Throwable;

interface PdoDatabaseInterface
{
    /**
     * Execute a statement.
     *
     * @param string $statement The statement.
     * @param array $bindings Any bindings for the statement.
     * @return PDOStatement
     */    
    public function execute(string $statement, array $bindings = []): PDOStatement;

    /**
     * Begin a transaction.
     *
     * @return bool Returns true on success or false on failure.
     */
    public function begin(): bool;
    
    /**
     * Commit a transaction.
     *
     * @return bool Returns true on success or false on failure.
     */
    public function commit(): bool;

    /**
     * Rollback a transaction.
     *
     * @return bool Returns true on success or false on failure.
     */
    public function rollback(): bool;
    
    /**
     * Returns true if supporting nested transactions, otherwise false.
     *
     * @return bool
     */
    public function supportsNestedTransactions(): bool;
    
    /**
     * Execute a transaction.
     *
     * @param callable $callback
     * @return void
     * @throws Throwable
     */
    public function transaction(callable $callback): void;
    
    /**
     * Get the pdo
     *
     * @return PDO
     */    
    public function pdo(): PDO;
}