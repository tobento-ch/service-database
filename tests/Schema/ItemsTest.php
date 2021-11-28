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

namespace Tobento\Service\Database\Test\Schema;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Database\Schema\ItemsInterface;
use Tobento\Service\Database\Schema\Items;

/**
 * ItemsTest
 */
class ItemsTest extends TestCase
{    
    public function testImplementsItemsInterface()
    {
        $items = new Items();
        
        $this->assertInstanceOf(ItemsInterface::class, $items);
    }
    
    public function testChunkMethods()
    {
        $items = new Items();
        
        $this->assertSame(10, $items->getChunkLength());
        
        $items->chunk(15);
        $this->assertSame(15, $items->getChunkLength());
    }
    
    public function testTransactionMethods()
    {
        $items = new Items();
        
        $this->assertTrue($items->withTransaction());
        
        $items->useTransaction(false);
        $this->assertFalse($items->withTransaction());
        
        $items->useTransaction(true);
        $this->assertTrue($items->withTransaction());        
    }
    
    public function testForceInsertMethods()
    {
        $items = new Items();
        
        $this->assertFalse($items->forcingInsert());
        
        $items->forceInsert(true);
        $this->assertTrue($items->forcingInsert());
        
        $items->forceInsert(false);
        $this->assertFalse($items->forcingInsert());        
    }    
    
    public function testIteration()
    {
        $items = new Items([
            ['name' => 'foo'],
            ['name' => 'bar'],
        ]);
        
        $created = [];
        
        foreach($items as $item)
        {
            $created[] = $item;        
        }
        
        $this->assertSame(
            [
                ['name' => 'foo'],
                ['name' => 'bar'],
            ],
            $created
        );         
    }
}