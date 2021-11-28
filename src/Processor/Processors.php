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

use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Database\DatabaseInterface;

/**
 * Processors
 */
class Processors implements ProcessorInterface
{
    /**
     * @var array<int, ProcessorInterface>
     */
    protected array $processors = [];

    /**
     * Create a new Processors.
     *
     * @param ProcessorInterface $processor
     */    
    public function __construct(
        ProcessorInterface ...$processor,
    ) {
        $this->processors = $processor;
    }

    /**
     * Returns true if the processor supports the database, otherwise false.
     *
     * @param DatabaseInterface $database
     * @return bool
     */
    public function supportsDatabase(DatabaseInterface $database): bool
    {        
        foreach($this->processors as $processor)
        {
            if ($processor->supportsDatabase($database)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Process a table schema for the specified database.
     *
     * @param Table $table
     * @param DatabaseInterface $database
     * @return void
     *
     * @throws ProcessException
     */    
    public function process(Table $table, DatabaseInterface $database): void
    {
        foreach($this->processors as $processor)
        {
            if ($processor->supportsDatabase($database)) {
                $processor->process($table, $database);
                return;
            }
        }
        
        throw new ProcessException('Unsupported database');
    }
}