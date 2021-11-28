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
use Tobento\Service\Database\Schema\ItemFactory;

/**
 * ItemFactoryTest
 */
class ItemFactoryTest extends TestCase
{    
    public function testImplementsItemsInterface()
    {
        $items = new ItemFactory(function() {
            return [];
        });
        
        $this->assertInstanceOf(ItemsInterface::class, $items);
    }
    
    public function testChunkMethods()
    {
        $items = new ItemFactory(function() {
            return ['foo' => 'bar'];
        });
        
        $this->assertSame(10, $items->getChunkLength());
        
        $items->chunk(15);
        $this->assertSame(15, $items->getChunkLength());
    }
    
    public function testTransactionMethods()
    {
        $items = new ItemFactory(function() {
            return ['foo' => 'bar'];
        });
        
        $this->assertTrue($items->withTransaction());
        
        $items->useTransaction(false);
        $this->assertFalse($items->withTransaction());
        
        $items->useTransaction(true);
        $this->assertTrue($items->withTransaction());        
    }
    
    public function testForceInsertMethods()
    {
        $items = new ItemFactory(function() {
            return ['foo' => 'bar'];
        });
        
        $this->assertFalse($items->forcingInsert());
        
        $items->forceInsert(true);
        $this->assertTrue($items->forcingInsert());
        
        $items->forceInsert(false);
        $this->assertFalse($items->forcingInsert());        
    }    
    
    public function testCreateMethod()
    {
        $items = new ItemFactory(function() {
            return ['foo' => 'bar'];
        });
        
        $items->create(2);
        
        $created = [];
        
        foreach($items as $item)
        {
            $created[] = $item;        
        }
        
        $this->assertSame(
            [
                ['foo' => 'bar'],
                ['foo' => 'bar'],
            ],
            $created
        );         
    }
    
    public function testCreateMethodWithZeroNumber()
    {
        $items = new ItemFactory(function() {
            return ['foo' => 'bar'];
        });
        
        $items->create(0);
        
        $created = [];
        
        foreach($items as $item)
        {
            $created[] = $item;        
        }
        
        $this->assertSame(
            [],
            $created
        );         
    }
    
    public function testCreateMethodWithNoItems()
    {
        $items = new ItemFactory(function() {
            return [];
        });
        
        $items->create(2);
        
        $created = [];
        
        foreach($items as $item)
        {
            $created[] = $item;        
        }
        
        $this->assertSame(
            [[], []],
            $created
        );         
    }
    
    public function testCreateMethodWithNullItems()
    {
        $items = new ItemFactory(function() {
            return null;
        });
        
        $items->create(2);
        
        $created = [];
        
        foreach($items as $item)
        {
            $created[] = $item;        
        }
        
        $this->assertSame(
            [null, null],
            $created
        );         
    }     
}