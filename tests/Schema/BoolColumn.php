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
use Tobento\Service\Database\Schema\BoolColumn;
use Tobento\Service\Database\Schema\Lengthable;
use Tobento\Service\Database\Schema\Defaultable;

/**
 * BoolColumnTest
 */
class BoolColumnTest extends TestCase
{    
    public function testImplementsInterfaces()
    {
        $column = new BoolColumn('name');
        
        $this->assertInstanceOf(ColumnInterface::class, $column);
        $this->assertInstanceOf(Lengthable::class, $column);
        $this->assertInstanceOf(Defaultable::class, $column);
    }
    
    public function testGetTypeMethod()
    {
        $column = new BoolColumn('name');
        
        $this->assertSame('bool', $column->getType());
    }
    
    public function testGetNameAndWithNameMethod()
    {
        $column = new BoolColumn('name');
        $this->assertSame('name', $column->getName());
        
        $newColumn = $column->withName('new');
        
        $this->assertFalse($column === $newColumn);
        $this->assertSame('name', $column->getName());
        $this->assertSame('new', $newColumn->getName());
    }
    
    public function testParameterMethod()
    {
        $column = new BoolColumn('name');
        $column->parameter('name', 'value');

        $this->assertSame('value', $column->getParameter('name'));
        $this->assertSame(null, $column->getParameter('foo'));
        $this->assertSame('bar', $column->getParameter('foo', 'bar'));
    }
    
    public function testLengthable()
    {
        $column = new BoolColumn('name');
        
        $this->assertSame(18, $column->getLength());
        
        $column->length(15);
        
        $this->assertSame(15, $column->getLength());
    }
    
    public function testDefaultable()
    {
        $column = new BoolColumn('name');
        
        $this->assertSame(null, $column->getDefault());
        
        $column->default('foo');
        
        $this->assertSame('foo', $column->getDefault());
    } 
}