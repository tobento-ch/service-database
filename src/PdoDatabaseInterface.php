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
     * Get the pdo
     *
     * @return PDO
     */    
    public function pdo(): PDO;
}