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
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Database\Schema\StringColumn;
use Tobento\Service\Database\Schema\Index;
use Tobento\Service\Database\Schema\Items;
use Tobento\Service\Database\Schema\ItemsInterface;

/**
 * TableTest tests
 */
class TableTest extends TestCase
{    
    public function testGetNameMethod()
    {            
        $this->assertSame(
            'users',
            (new Table('users'))->getName()
        ); 
    }
    
    public function testAddColumnMethod()
    {
        $table = new Table('users');
        
        $this->assertSame(
            $table,
            $table->addColumn(new StringColumn('email'))
        ); 
    }
    
    public function testGetColumnsMethod()
    {
        $table = new Table('users');
        
        $email = new StringColumn('email');
        $name = new StringColumn('name');
        
        $table->addColumn($email);
        $table->addColumn($name);
            
        $this->assertSame(
            [
                'email' => $email,
                'name' => $name,
            ],
            $table->getColumns()
        ); 
    }
    
    public function testGetColumnMethod()
    {
        $table = new Table('users');
        
        $email = new StringColumn('email');
        
        $table->addColumn($email);
            
        $this->assertSame(
            $email,
            $table->getColumn('email')
        );
        
        $this->assertSame(
            null,
            $table->getColumn('name')
        );        
    }
    
    public function testAddIndexMethod()
    {
        $table = new Table('users');
        
        $this->assertSame(
            $table,
            $table->addIndex(new Index('name'))
        ); 
    }
    
    public function testGetIndexesMethod()
    {
        $table = new Table('users');
        
        $email = new Index('email');
        $name = new Index('name');
        
        $table->addIndex($email);
        $table->addIndex($name);
            
        $this->assertSame(
            [
                'email' => $email,
                'name' => $name,
            ],
            $table->getIndexes()
        ); 
    }

    public function testGetIndexMethod()
    {
        $table = new Table('users');
        
        $email = new Index('email');
        
        $table->addIndex($email);
            
        $this->assertSame(
            $email,
            $table->getIndex('email')
        );
        
        $this->assertSame(
            null,
            $table->getIndex('name')
        );        
    }
    
    public function testItemsMethod()
    {
        $table = new Table('users');
        
        $this->assertSame(
            null,
            $table->getItems()
        );

        $table->items([
            ['name' => 'Foo', 'active' => true],
            ['name' => 'Bar', 'active' => true],
        ]);
        
        $this->assertInstanceOf(
            ItemsInterface::class,
            $table->getItems()
        );        
    }
    
    public function testItemsCountMethod()
    {
        $table = new Table('users');
        
        $this->assertSame(
            $table,
            $table->itemsCount(4)
        );        
    }
    
    public function testGetItemsCountMethod()
    {
        $table = new Table('users');
        
        $this->assertSame(0, $table->getItemsCount());
        
        $table->itemsCount(4);
        
        $this->assertSame(4, $table->getItemsCount());
    }
    
    public function testParameterMethod()
    {
        $table = new Table('users');
        
        $this->assertSame($table, $table->parameter('name', 'value'));
        $this->assertSame($table, $table->parameter('number', 5));
        $this->assertSame($table, $table->parameter('bool', true));
    } 
    
    public function testGetParameterMethod()
    {
        $table = new Table('users');
        
        $table->parameter('name', 'value');
            
        $this->assertSame('value', $table->getParameter('name'));
        $this->assertSame(null, $table->getParameter('number'));
        $this->assertSame('default', $table->getParameter('bool', 'default'));
    }
    
    public function testIndexMethod()
    {
        $table = new Table('users');

        $this->assertInstanceOf(
            Index::class,
            $table->index()
        );
        
        $this->assertInstanceOf(
            Index::class,
            $table->index('name')
        );        
    }      
}