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

use ArrayIterator;

/**
 * Items
 */
class Items extends ArrayIterator implements ItemsInterface
{
    /**
     * @var int
     */
    protected int $chunk = 10;
    
    /**
     * @var bool
     */
    protected bool $useTransaction = true;
    
    /**
     * @var bool
     */
    protected bool $forceInsert = false;
    
    /**
     * Set the chunk length.
     *
     * @param int $length
     * @return static $this
     */    
    public function chunk(int $length): static
    {
        $this->chunk = $length;
        return $this;
    }
    
    /**
     * Set if to use transaction.
     *
     * @param bool $use
     * @return static $this
     */    
    public function useTransaction(bool $use): static
    {
        $this->useTransaction = $use;
        return $this;
    }
    
    /**
     * Set if to use force insert.
     *
     * @param bool $forceInsert
     * @return static $this
     */    
    public function forceInsert(bool $forceInsert): static
    {
        $this->forceInsert = $forceInsert;
        return $this;
    }
    
    /**
     * Returns the chunk length.
     *
     * @return int
     */    
    public function getChunkLength(): int
    {
        return $this->chunk;
    }
    
    /**
     * Returns whether to use transaction.
     *
     * @return bool
     */    
    public function withTransaction(): bool
    {
        return $this->useTransaction;
    }

    /**
     * Returns whether to force insert.
     *
     * @return bool
     */    
    public function forcingInsert(): bool
    {
        return $this->forceInsert;
    }    
}