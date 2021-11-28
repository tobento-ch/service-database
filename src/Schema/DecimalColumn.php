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
 * DecimalColumn
 */
class DecimalColumn extends Column implements
    Lengthable,
    Nullable,
    Defaultable
{
    use HasLengthable;
    use HasNullable;
    use HasDefaultable;
    
    /**
     * Create a new DecimalColumn.
     *
     * @param string $name The name of the column.
     * @param int $precision
     * @param int $scale
     */    
    public function __construct(
        protected string $name,
        int $precision = 10,
        int $scale = 0,
    ) {
        $this->length = (string)$precision.','.(string)$scale;
    }
    
    /**
     * Returns the column type.
     *
     * @return string
     */    
    public function getType(): string
    {
        return 'decimal';
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
}