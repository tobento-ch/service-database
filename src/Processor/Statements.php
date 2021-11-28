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
use IteratorAggregate;
use ArrayIterator;

/**
 * Statements
 */
class Statements implements IteratorAggregate
{
    /**
     * Create a new Statements.
     *
     * @param array<int, Statement> $statements
     * @param Table $table
     */    
    public function __construct(
        protected array $statements,
        protected Table $table,
    ) {}
    
    /**
     * Returns the statements.
     *
     * @return array<int, Statement>
     */    
    public function getStatements(): array
    {
        return $this->statements;
    }
    
    /**
     * Returns the table.
     *
     * @return Table
     */    
    public function getTable(): Table
    {
        return $this->table;
    }
    
    /**
     * Returns an iterator for the statements.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->statements);
    }    
}