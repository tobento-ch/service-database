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
use Tobento\Service\Database\Schema\BigIntColumn;
use Tobento\Service\Database\Schema\Lengthable;
use Tobento\Service\Database\Schema\Nullable;
use Tobento\Service\Database\Schema\Defaultable;
use Tobento\Service\Database\Schema\Unsignable;

/**
 * BigIntColumnTest
 */
class BigIntColumnTest extends TestCase
{    
    public function testImplementsInterfaces()
    {
        $column = new BigIntColumn('name');
        
        $this->assertInstanceOf(ColumnInterface::class, $column);
        $this->assertInstanceOf(Lengthable::class, $column);
        $this->assertInstanceOf(Nullable::class, $column);
        $this->assertInstanceOf(Defaultable::class, $column);
        $this->assertInstanceOf(Unsignable::class, $column);
    }
    
    public function testGetTypeMethod()
    {
        $column = new BigIntColumn('name');
        
        $this->assertSame('bigInt', $column->getType());
    }
    
    public function testGetNameAndWithNameMethod()
    {
        $column = new BigIntColumn('name');
        $this->assertSame('name', $column->getName());
        
        $newColumn = $column->withName('new');
        
        $this->assertFalse($column === $newColumn);
        $this->assertSame('name', $column->getName());
        $this->assertSame('new', $newColumn->getName());
    }
    
    public function testParameterMethod()
    {
        $column = new BigIntColumn('name');
        $column->parameter('name', 'value');

        $this->assertSame('value', $column->getParameter('name'));
        $this->assertSame(null, $column->getParameter('foo'));
        $this->assertSame('bar', $column->getParameter('foo', 'bar'));
    }
    
    public function testLengthable()
    {
        $column = new BigIntColumn('name', 18);
        
        $this->assertSame(18, $column->getLength());
        
        $column->length(15);
        
        $this->assertSame(15, $column->getLength());
    }
    
    public function testNullable()
    {
        $column = new BigIntColumn('name', 18);
        
        $this->assertTrue($column->isNullable());
        
        $column->nullable(false);
        
        $this->assertFalse($column->isNullable());
        
        $column->nullable(true);
        
        $this->assertTrue($column->isNullable());
    }
    
    public function testDefaultable()
    {
        $column = new BigIntColumn('name', 18);
        
        $this->assertSame(null, $column->getDefault());
        
        $column->default('foo');
        
        $this->assertSame('foo', $column->getDefault());
    }
    
    public function testUnsignable()
    {
        $column = new BigIntColumn('name', 18);
        
        $this->assertTrue($column->isUnsigned());
        
        $column->unsigned(false);
        
        $this->assertFalse($column->isUnsigned());
        
        $column->unsigned(true);
        
        $this->assertTrue($column->isUnsigned());
    }    
}