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

use IteratorAggregate;

/**
 * ItemsInterface
 */
interface ItemsInterface extends IteratorAggregate
{
    /**
     * Set the chunk length.
     *
     * @param int $length
     * @return static $this
     */    
    public function chunk(int $length): static;
    
    /**
     * Set if to use transaction.
     *
     * @param bool $use
     * @return static $this
     */
    public function useTransaction(bool $use): static;
    
    /**
     * Set if to use force insert.
     *
     * @param bool $forceInsert
     * @return static $this
     */
    public function forceInsert(bool $forceInsert): static;
    
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