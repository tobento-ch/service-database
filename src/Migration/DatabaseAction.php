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

namespace Tobento\Service\Database\Migration;

use Tobento\Service\Migration\ActionInterface;
use Tobento\Service\Migration\ActionFailedException;
use Tobento\Service\Database\Processor\ProcessorInterface;
use Tobento\Service\Database\Processor\ProcessException;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\Schema\Table;

/**
 * DatabaseAction
 */
class DatabaseAction implements ActionInterface
{
    /**
     * Create a new DatabaseAction.
     *
     * @param ProcessorInterface $processor
     * @param DatabaseInterface $database
     * @param Table $table
     * @param string $description A description of the action.
     */    
    public function __construct(
        protected ProcessorInterface $processor,
        protected DatabaseInterface $database,
        protected Table $table,
        protected string $description = '',
    ) {}
    
    /**
     * Process the action.
     *
     * @return void
     * @throws ActionFailedException
     */    
    public function process(): void
    {        
        try {
            $this->processor->process($this->table, $this->database);
        } catch (ProcessException $e) {
            throw new ActionFailedException(
                $this,
                'Database Action Failed!',
                0,
                $e
            );
        }
    }
 
    /**
     * Returns a description of the action.
     *
     * @return string
     */    
    public function description(): string
    {
        return $this->description;
    }
    
    /**
     * Returns the processed data information.
     *
     * @return array<array-key, string>
     */
    public function processedDataInfo(): array
    {
        return [
            'database' => $this->database->name(),
            'table' => $this->table->getName(),
        ];
    }
}