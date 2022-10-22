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
use Tobento\Service\Database\Schema\IndexFactoryInterface;
use Tobento\Service\Database\Schema\IndexFactory;
use Tobento\Service\Database\Schema\IndexInterface;
use Tobento\Service\Database\Schema\CreateIndexException;

/**
 * IndexFactoryTest
 */
class IndexFactoryTest extends TestCase
{
    public function testImplementsInterfaces()
    {
        $this->assertInstanceOf(IndexFactoryInterface::class, new IndexFactory());
    }
    
    public function testCreateIndexMethod()
    {
        $index = (new IndexFactory())->createIndex(name: 'foo');
        
        $this->assertInstanceOf(IndexInterface::class, $index);
        $this->assertSame('foo', $index->getName());
    }
    
    public function testCreateIndexFromArrayMethodThrowsExceptionIfNameIsInvalid()
    {
        $this->expectException(CreateIndexException::class);
        
        $index = (new IndexFactory())->createIndexFromArray(['name' => []]);
    }
    
    public function testCreateIndexFromArrayMethodThrowsExceptionIfNameIsNotSpecified()
    {
        $this->expectException(CreateIndexException::class);
        
        $index = (new IndexFactory())->createIndexFromArray([]);
    }
    
    public function testCreateIndexFromArrayMethod()
    {
        $index = (new IndexFactory())->createIndexFromArray(['name' => 'foo']);
        
        $this->assertInstanceOf(IndexInterface::class, $index);
        $this->assertSame('foo', $index->getName());
    }
    
    public function testCreateIndexFromArrayMethodStringColumn()
    {
        $index = (new IndexFactory())->createIndexFromArray([
            'name' => 'foo',
            'column' => 'name',
        ]);
        
        $this->assertSame(['name'], $index->getColumns());
    }
    
    public function testCreateIndexFromArrayMethodArrayColumn()
    {
        $index = (new IndexFactory())->createIndexFromArray([
            'name' => 'foo',
            'column' => ['name', 'another_name'],
        ]);
        
        $this->assertSame(['name', 'another_name'], $index->getColumns());
    }
    
    public function testCreateIndexFromArrayMethodUnique()
    {
        $index = (new IndexFactory())->createIndexFromArray([
            'name' => 'foo',
            'unique' => true,
        ]);
        
        $this->assertTrue($index->isUnique());
        
        $index = (new IndexFactory())->createIndexFromArray([
            'name' => 'foo',
            'unique' => false,
        ]);
        
        $this->assertFalse($index->isUnique());
    }

    public function testCreateIndexFromArrayMethodPrimary()
    {
        $index = (new IndexFactory())->createIndexFromArray([
            'name' => 'foo',
            'primary' => true,
        ]);
        
        $this->assertTrue($index->isPrimary());
        
        $index = (new IndexFactory())->createIndexFromArray([
            'name' => 'foo',
            'primary' => false,
        ]);
        
        $this->assertFalse($index->isPrimary());
    }
    
    public function testCreateIndexFromArrayMethodRename()
    {
        $index = (new IndexFactory())->createIndexFromArray([
            'name' => 'foo',
            'rename' => 'new',
        ]);
        
        $this->assertSame('new', $index->getRename());
    }
    
    public function testCreateIndexFromArrayMethodDrop()
    {
        $index = (new IndexFactory())->createIndexFromArray([
            'name' => 'foo',
            'drop' => true,
        ]);
        
        $this->assertTrue($index->dropping());
        
        $index = (new IndexFactory())->createIndexFromArray([
            'name' => 'foo',
            'drop' => false,
        ]);
        
        $this->assertFalse($index->dropping());
    }
}