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
use Tobento\Service\Database\Schema\IndexInterface;
use Tobento\Service\Database\Schema\Index;

/**
 * IndexTest
 */
class IndexTest extends TestCase
{    
    public function testImplementsInterfaces()
    {
        $index = new Index('name');
        
        $this->assertInstanceOf(IndexInterface::class, $index);
    }
    
    public function testGetTypeMethod()
    {
        $index = new Index('name');
        
        $this->assertSame('index', $index->getType());
    }
    
    public function testGetNameAndWithNameMethod()
    {
        $index = new Index('name');
        $this->assertSame('name', $index->getName());
        
        $newIndex = $index->withName('new');
        
        $this->assertFalse($index === $newIndex);
        $this->assertSame('name', $index->getName());
        $this->assertSame('new', $newIndex->getName());
    }
    
    public function testWithNameMethodWithRename()
    {
        $index = new Index('name');
        $index->rename('rename');
        $newIndex = $index->withName('new');
        $this->assertSame('new', $newIndex->getName());
        $this->assertSame(null, $newIndex->getRename());
    }
    
    public function testColumnAndGetColumnsMethod()
    {
        $index = new Index('name');
        $index->column('foo', 'bar');      
        $this->assertSame(['foo', 'bar'], $index->getColumns());
    }

    public function testUnique()
    {
        $index = new Index('name');
        
        $this->assertFalse($index->isUnique());
        
        $index->unique(true);
        
        $this->assertTrue($index->isUnique());
        
        $index->unique(false);
        
        $this->assertFalse($index->isUnique());
    }
    
    public function testPrimary()
    {
        $index = new Index('name');
        
        $this->assertFalse($index->isPrimary());
        
        $index->primary(true);
        
        $this->assertTrue($index->isPrimary());
        
        $index->primary(false);
        
        $this->assertFalse($index->isPrimary());
    }

    public function testRename()
    {
        $index = new Index('name');
        $this->assertSame(null, $index->getRename());
        
        $index->rename('rename');
        $this->assertSame('rename', $index->getRename());
    }
    
    public function testDropping()
    {
        $index = new Index('name');
        
        $this->assertFalse($index->dropping());
        
        $index->drop(true);
        
        $this->assertTrue($index->dropping());
        
        $index->drop(false);
        
        $this->assertFalse($index->dropping());
    }    
}