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

use Iterator;

/**
 * ItemsInterface
 */
interface ItemsInterface extends Iterator
{
    /**
     * Returns the chunk length.
     *
     * @return int
     */    
    public function getChunkLength(): int;
    
    /**
     * Returns whether to use transaction.
     *
     * @return bool
     */    
    public function withTransaction(): bool;
    
    /**
     * Returns whether to force insert.
     *
     * @return bool
     */    
    public function forcingInsert(): bool;
}