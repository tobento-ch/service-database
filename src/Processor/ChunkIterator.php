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

namespace Tobento\Service\Database\Processor;

use IteratorAggregate;
use Iterator;
use Generator;

/**
 * ChunkIterator
 */
class ChunkIterator implements IteratorAggregate
{
    /**
     * Create a new ChunkIterator.
     *
     * @param Iterator $iterator
     * @param int $chunkLength
     */    
    public function __construct(
        protected Iterator $iterator,
        protected int $chunkLength,
    ) {}
    
    /**
     * Returns an iterator for the items.
     *
     * @return Generator
     *
     * @psalm-suppress UnusedVariable
     */
    public function getIterator(): Generator
    {
        $chunk = [];

        for($i = 0; $this->iterator->valid(); $i++){
            $chunk[] = $this->iterator->current();
            $this->iterator->next();
            if(count($chunk) == $this->chunkLength){
                yield $chunk;
                $chunk = [];
            }
        }

        if(count($chunk)){
            yield $chunk;
        }
    } 
}