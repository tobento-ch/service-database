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
use Tobento\Service\Database\Schema\RenameColumn;

/**
 * RenameColumnTest
 */
class RenameColumnTest extends TestCase
{    
    public function testImplementsInterfaces()
    {
        $column = new RenameColumn('name', 'new');
        
        $this->assertInstanceOf(ColumnInterface::class, $column);
    }
    
    public function testGetTypeMethod()
    {
        $column = new RenameColumn('name', 'new');
        
        $this->assertSame('rename', $column->getType());
    }
    
    public function testGetNewNameMethod()
    {
        $column = new RenameColumn('name', 'new');
        
        $this->assertSame('new', $column->getNewNAme());
    }    
    
    public function testGetNameAndWithNameMethod()
    {
        $column = new RenameColumn('name', 'new');
        $this->assertSame('name', $column->getName());
        
        $newColumn = $column->withName('new');
        
        $this->assertFalse($column === $newColumn);
        $this->assertSame('name', $column->getName());
        $this->assertSame('new', $newColumn->getName());
    }
    
    public function testParameterMethod()
    {
        $column = new RenameColumn('name', 'new');
        $column->parameter('name', 'value');

        $this->assertSame('value', $column->getParameter('name'));
        $this->assertSame(null, $column->getParameter('foo'));
        $this->assertSame('bar', $column->getParameter('foo', 'bar'));
    }
}