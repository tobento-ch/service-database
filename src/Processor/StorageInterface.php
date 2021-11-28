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

namespace Tobento\Service\Database\Processor;

use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\Schema\Table;

/**
 * StorageInterface
 */
interface StorageInterface
{
    /**
     * Returns true if the storage supports the database, otherwise false.
     *
     * @param DatabaseInterface $database
     * @return bool
     */
    public function supportsDatabase(DatabaseInterface $database): bool;
    
    /**
     * Returns the specified table if exist, otherwise null.
     *
     * @param DatabaseInterface $database
     * @param string $name The table name
     * @return null|Table
     * @throws StorageFetchException
     */    
    public function fetchTable(DatabaseInterface $database, string $table): null|Table;
    
    /**
     * Store the table.
     *
     * @param DatabaseInterface $database
     * @param string $name
     * @return void
     * @throws StorageStoreException
     */    
    public function storeTable(DatabaseInterface $database, Table $table): void;
}