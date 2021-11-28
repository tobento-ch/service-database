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
 * Nullable
 */
interface Nullable
{    
    /**
     * Set if the column is nullable.
     *
     * @param bool $nullable
     * @return static $this
     */    
    public function nullable(bool $nullable = true): static;
    
    /**
     * Returns true if the column is nullable, otherwise false.
     *
     * @return bool
     */    
    public function isNullable(): bool;
}