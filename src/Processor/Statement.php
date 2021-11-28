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

/**
 * Statement
 */
class Statement
{
    /**
     * Create a new Statement.
     *
     * @param string $statement The statement.
     * @param array $bindings Any bindings for the statement.
     * @param bool $transactionable
     */    
    public function __construct(
        protected string $statement,
        protected array $bindings,
        protected bool $transactionable,
    ) {}
    
    /**
     * Returns the statement.
     *
     * @return string
     */    
    public function getStatement(): string
    {
        return $this->statement;
    }
    
    /**
     * Returns the bindings.
     *
     * @return array
     */    
    public function getBindings(): array
    {
        return $this->bindings;
    }
    
    /**
     * Returns whether the stamtement is transactionable.
     *
     * @return bool
     */    
    public function isTransactionable(): bool
    {
        return $this->transactionable;
    }    
}