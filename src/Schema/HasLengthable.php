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
 * HasLengthable
 */
trait HasLengthable
{
    /**
     * @var int|string
     */
    protected int|string $length = 1;
    
    /**
     * Set the column length.
     *
     * @param int|string $length
     * @return static $this
     */    
    public function length(int|string $length): static
    {
        $this->length = $length;
        return $this;
    }
    
    /**
     * Returns the column length.
     *
     * @return int|string
     */    
    public function getLength(): int|string
    {
        return $this->length;
    }
}