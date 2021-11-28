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
 * BoolColumn
 */
class BoolColumn extends Column implements
    Lengthable,
    Defaultable
{
    use HasDefaultable;
        
    /**
     * Create a new BoolColumn.
     *
     * @param string $name The name of the column.
     */
    public function __construct(
        protected string $name
    ) {}
    
    /**
     * Returns the column type.
     *
     * @return string
     */    
    public function getType(): string
    {
        return 'bool';
    }
    
    /**
     * Set the column length.
     *
     * @param int|string $length
     * @return static $this
     */    
    public function length(int|string $length): static
    {
        return $this;
    }
    
    /**
     * Returns the column length.
     *
     * @return int|string
     */    
    public function getLength(): int|string
    {
        return 1;
    }
    
    /**
     * Returns the default value if any.
     *
     * @return mixed
     */    
    public function getDefault(): mixed
    {
        return $this->default ? 1 : 0;
    }
}