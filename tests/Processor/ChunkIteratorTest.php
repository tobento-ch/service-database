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

namespace Tobento\Service\Database\Test\Processor;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Database\Processor\ChunkIterator;
use IteratorAggregate;
use Tobento\Service\Database\Schema\Items;
use Tobento\Service\Database\Schema\ItemFactory;

/**
 * ChunkIteratorTest
 */
class ChunkIteratorTest extends TestCase
{    
    public function testImplementsIteratorAggregate()
    {
        $chunks = new ChunkIterator(new Items(), 2);
        
        $this->assertInstanceOf(IteratorAggregate::class, $chunks);
    }
    
    public function testChunk()
    {
        $items = new ItemFactory(function() {
            return ['name' => 'foo'];
        });
        
        $items->create(number: 10);
        
        $chunks = new ChunkIterator(iterator: $items, chunkLength: 2);
        
        $chunked = [];
        
        foreach($chunks as $item)
        {
            $chunked[] = $item;        
        }
        
        $this->assertSame(5, count($chunked));         
    }
    
    public function testChunkWithHigherChunkLengthAsItems()
    {
        $items = new ItemFactory(function() {
            return ['name' => 'foo'];
        });
        
        $items->create(number: 10);
        
        $chunks = new ChunkIterator(iterator: $items, chunkLength: 100);
        
        $chunked = [];
        
        foreach($chunks as $item)
        {
            $chunked[] = $item;        
        }
        
        $this->assertSame(1, count($chunked));         
    }
    
    public function testChunkWithNoItems()
    {
        $items = new ItemFactory(function() {
            return ['name' => 'foo'];
        });
        
        $items->create(number: 0);
        
        $chunks = new ChunkIterator(iterator: $items, chunkLength: 100);
        
        $chunked = [];
        
        foreach($chunks as $item)
        {
            $chunked[] = $item;        
        }
        
        $this->assertSame(0, count($chunked));         
    }    
}