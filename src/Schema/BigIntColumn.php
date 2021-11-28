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
 * BigIntColumn
 */
class BigIntColumn extends Column implements
    Lengthable,
    Nullable,
    Defaultable,
    Unsignable
{
    use HasLengthable;
    use HasNullable;
    use HasDefaultable;
    use HasUnsignable;
    
    /**
     * Create a new BigIntColumn.
     *
     * @param string $name The name of the column.
     * @param int $length
     */    
    public function __construct(
        protected string $name,
        int $length = 20,
    ) {
        $this->length($length);
    }
 
    /**
     * Returns the column type.
     *
     * @return string
     */    
    public function getType(): string
    {
        return 'bigInt';
    } 
}