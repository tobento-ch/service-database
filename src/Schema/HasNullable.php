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

namespace Tobento\Service\Database\Schema;

/**
 * HasNullable
 */
trait HasNullable
{
    /**
     * @var bool
     */
    protected bool $nullable = true;
    
    /**
     * Set if the column is nullable.
     *
     * @param bool $nullable
     * @return static $this
     */    
    public function nullable(bool $nullable = true): static
    {
        $this->nullable = $nullable;
        return $this;
    }
    
    /**
     * Returns true if the column is nullable, otherwise false.
     *
     * @return bool
     */    
    public function isNullable(): bool
    {
        return $this->nullable;
    }  
}