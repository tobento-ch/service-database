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
 * ProcessorInterface
 */
interface ProcessorInterface
{
    /**
     * Returns true if the processor supports the database, otherwise false.
     *
     * @param DatabaseInterface $database
     * @return bool
     */
    public function supportsDatabase(DatabaseInterface $database): bool;
    
    /**
     * Process a table schema for the specified database.
     *
     * @param Table $table
     * @param DatabaseInterface $database
     * @return void
     *
     * @throws ProcessException
     */    
    public function process(Table $table, DatabaseInterface $database): void;   
}