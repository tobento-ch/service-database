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
use Tobento\Service\Database\Schema\JsonFileItems;

/**
 * JsonFileItemsTest
 */
class JsonFileItemsTest extends TestCase
{    
    public function testImplementsItemsInterface()
    {
        $items = new JsonFileItems(__DIR__.'/../src/countries.json');
        
        $this->assertInstanceOf(ItemsInterface::class, $items);
    }
    
    public function testChunkMethods()
    {
        $items = new JsonFileItems(__DIR__.'/../src/countries.json');
        
        $this->assertSame(10, $items->getChunkLength());
        
        $items->chunk(15);
        $this->assertSame(15, $items->getChunkLength());
    }
    
    public function testTransactionMethods()
    {
        $items = new JsonFileItems(__DIR__.'/../src/countries.json');
        
        $this->assertTrue($items->withTransaction());
        
        $items->useTransaction(false);
        $this->assertFalse($items->withTransaction());
        
        $items->useTransaction(true);
        $this->assertTrue($items->withTransaction());        
    }
    
    public function testForceInsertMethods()
    {
        $items = new JsonFileItems(__DIR__.'/../src/countries.json');
        
        $this->assertFalse($items->forcingInsert());
        
        $items->forceInsert(true);
        $this->assertTrue($items->forcingInsert());
        
        $items->forceInsert(false);
        $this->assertFalse($items->forcingInsert());        
    }    
    
    public function testIteration()
    {
        $items = new JsonFileItems(__DIR__.'/../src/countries.json');
        
        $created = [];
        
        foreach($items as $item)
        {
            $created[] = $item;        
        }
        
        $this->assertSame(
            [
                ['iso' => 'DE', 'country' => 'Germany'],
                ['iso' => 'CH', 'country' => 'Switzerland'],
                ['iso' => 'US', 'country' => 'United States'],
            ],
            $created
        );         
    }
    
    public function testMap()
    {
        $items = new JsonFileItems(__DIR__.'/../src/countries.json');
        
        $items->map(function($item) {
            return [
                'iso' => $item['iso'] ?? '',
                'name' => $item['country'] ?? '',
            ];
        });
        
        $created = [];
        
        foreach($items as $item)
        {
            $created[] = $item;        
        }
        
        $this->assertSame(
            [
                ['iso' => 'DE', 'name' => 'Germany'],
                ['iso' => 'CH', 'name' => 'Switzerland'],
                ['iso' => 'US', 'name' => 'United States'],
            ],
            $created
        );         
    }    
}