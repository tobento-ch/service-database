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
 * Storages
 */
class Storages implements StorageInterface
{
    /**
     * @var array<int, StorageInterface>
     */
    protected array $storages = [];
    
    /**
     * Create a new Storages.
     *
     * @param StorageInterface ...$storage
     */    
    public function __construct(
        StorageInterface ...$storage,
    ) {
        $this->storages = $storage;
    }
    
    /**
     * Returns true if the storage supports the database, otherwise false.
     *
     * @param DatabaseInterface $database
     * @return bool
     */
    public function supportsDatabase(DatabaseInterface $database): bool
    {
        foreach($this->storages as $storage)
        {
            if ($storage->supportsDatabase($database)) {
                return true;
            }
        }
        
        return false;
    }    
    
    /**
     * Returns the specified table if exist, otherwise null.
     *
     * @param DatabaseInterface $database
     * @param string $name The table name
     * @return null|Table
     * @throws StorageFetchException
     */    
    public function fetchTable(DatabaseInterface $database, string $table): null|Table
    {        
        foreach($this->storages as $storage)
        {
            if ($storage->supportsDatabase($database)) {
                return $storage->fetchTable($database, $table);
            }
        }
        
        throw new StorageFetchException('Unsupported database');
    }
    
    /**
     * Store the table.
     *
     * @param DatabaseInterface $database
     * @param string $name
     * @return void
     * @throws StorageStoreException
     */    
    public function storeTable(DatabaseInterface $database, Table $table): void
    {        
        foreach($this->storages as $storage)
        {
            if ($storage->supportsDatabase($database)) {
                $storage->storeTable($database, $table);
                return;
            }
        }
        
        throw new StorageStoreException('Unsupported database');
    }    
}