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
 * Lengthable
 */
interface Lengthable
{    
    /**
     * Returns the column length.
     *
     * @return int|string
     */    
    public function getLength(): int|string;
}