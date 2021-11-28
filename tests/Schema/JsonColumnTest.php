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
use Tobento\Service\Database\Schema\ColumnInterface;
use Tobento\Service\Database\Schema\JsonColumn;
use Tobento\Service\Database\Schema\Nullable;
use Tobento\Service\Database\Schema\Defaultable;

/**
 * JsonColumnTest
 */
class JsonColumnTest extends TestCase
{    
    public function testImplementsInterfaces()
    {
        $column = new JsonColumn('name');
        
        $this->assertInstanceOf(ColumnInterface::class, $column);
        $this->assertInstanceOf(Nullable::class, $column);
        $this->assertInstanceOf(Defaultable::class, $column);
    }
    
    public function testGetTypeMethod()
    {
        $column = new JsonColumn('name');
        
        $this->assertSame('json', $column->getType());
    }
    
    public function testGetNameAndWithNameMethod()
    {
        $column = new JsonColumn('name');
        $this->assertSame('name', $column->getName());
        
        $newColumn = $column->withName('new');
        
        $this->assertFalse($column === $newColumn);
        $this->assertSame('name', $column->getName());
        $this->assertSame('new', $newColumn->getName());
    }
    
    public function testParameterMethod()
    {
        $column = new JsonColumn('name');
        $column->parameter('name', 'value');

        $this->assertSame('value', $column->getParameter('name'));
        $this->assertSame(null, $column->getParameter('foo'));
        $this->assertSame('bar', $column->getParameter('foo', 'bar'));
    }
    
    public function testNullable()
    {
        $column = new JsonColumn('name');
        
        $this->assertTrue($column->isNullable());
        
        $column->nullable(false);
        
        $this->assertFalse($column->isNullable());
        
        $column->nullable(true);
        
        $this->assertTrue($column->isNullable());
    }
    
    public function testDefaultable()
    {
        $column = new JsonColumn('name');
        
        $this->assertSame(null, $column->getDefault());
        
        $column->default('foo');
        
        $this->assertSame('foo', $column->getDefault());
    }
}