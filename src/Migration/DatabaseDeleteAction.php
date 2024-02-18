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
use Closure;

/**
 * DatabaseDeleteAction
 */
class DatabaseDeleteAction implements ActionInterface
{
    /**
     * @var Table
     */
    protected Table $table;
    
    /**
     * Create a new DatabaseDeleteAction.
     *
     * @param ProcessorInterface $processor
     * @param DatabaseInterface $database
     * @param Table|Closure $table
     * @param null|string $name A name of the action.
     * @param string $description A description of the action.
     * @param string $type A type of the action.
     */
    public function __construct(
        protected ProcessorInterface $processor,
        protected DatabaseInterface $database,
        Table|Closure $table,
        protected null|string $name = null,
        protected string $description = '',
        protected string $type = 'database',
    ) {
        if ($table instanceof Closure) {
            $table = $table();
        }
        
        $this->table = $table;
    }
    
    /**
     * Process the action.
     *
     * @return void
     * @throws ActionFailedException
     */
    public function process(): void
    {
        try {
            $this->table->dropTable();
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
     * Returns a name of the action.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name ?: $this->table->getName();
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
     * Returns the type of the action.
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
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
    
    /**
     * Returns the processor.
     *
     * @return ProcessorInterface
     */
    public function processor(): ProcessorInterface
    {
        return $this->processor;
    }
    
    /**
     * Returns the database.
     *
     * @return DatabaseInterface
     */
    public function database(): DatabaseInterface
    {
        return $this->database;
    }
    
    /**
     * Returns the table.
     *
     * @return Table
     */
    public function table(): Table
    {
        return $this->table;
    }
}