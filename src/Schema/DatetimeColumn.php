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
 * DatetimeColumn
 */
class DatetimeColumn extends Column implements
    Nullable,
    Defaultable
{
    use HasNullable;
    use HasDefaultable;
    
    /**
     * Create a new DatetimeColumn.
     *
     * @param string $name The name of the column.
     */    
    public function __construct(
        protected string $name,
    ) {}
 
    /**
     * Returns the column type.
     *
     * @return string
     */    
    public function getType(): string
    {
        return 'datetime';
    }
}