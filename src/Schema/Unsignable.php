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
 * Unsignable
 */
interface Unsignable
{
    /**
     * Set if the column is unsigned.
     *
     * @param bool $nullable
     * @return static $this
     */    
    public function unsigned(bool $unsigned = true): static;
    
    /**
     * Returns true if the column is unsigned, otherwise false.
     *
     * @return bool
     */    
    public function isUnsigned(): bool;
}