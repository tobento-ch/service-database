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
use Tobento\Service\Database\Schema\ColumnFactoryInterface;
use Tobento\Service\Database\Schema\ColumnFactory;
use Tobento\Service\Database\Schema\ColumnInterface;
use Tobento\Service\Database\Schema\Lengthable;
use Tobento\Service\Database\Schema\Nullable;
use Tobento\Service\Database\Schema\Defaultable;
use Tobento\Service\Database\Schema\Unsignable;
use Tobento\Service\Database\Schema\CreateColumnException;

/**
 * ColumnFactoryTest
 */
class ColumnFactoryTest extends TestCase
{
    protected array $types = [
        'bigInt' => 'bigInt',
        'bigPrimary' => 'bigPrimary',
        'bool' => 'bool',
        'char' => 'char',
        'date' => 'date',
        'datetime' => 'datetime',
        'decimal' => 'decimal',
        'double' => 'double',
        'float' => 'float',
        'int' => 'int',
        'json' => 'json',
        'primary' => 'primary',
        'string' => 'string',
        'text' => 'text',
        'time' => 'time',
        'timestamp' => 'timestamp',
        'tinyInt' => 'tinyInt',
    ];
    
    public function testImplementsInterfaces()
    {
        $this->assertInstanceOf(ColumnFactoryInterface::class, new ColumnFactory());
    }
    
    public function testCreateColumnMethod()
    {
        $column = (new ColumnFactory())->createColumn(type: 'int', name: 'foo');
        
        $this->assertInstanceOf(ColumnInterface::class, $column);
        $this->assertSame('int', $column->getType());
        $this->assertSame('foo', $column->getName());
    }
    
    public function testCreateColumnMethodSupportedTypes()
    {
        foreach($this->types as $type) {
            $column = (new ColumnFactory())->createColumn(type: $type, name: 'foo');
            
            $this->assertInstanceOf(ColumnInterface::class, $column);
            $this->assertSame($type, $column->getType());
            $this->assertSame('foo', $column->getName());
        }
    }
    
    public function testCreateColumnMethodThrowsExceptionIfTypeIsNotValid()
    {
        $this->expectException(CreateColumnException::class);
        
        $column = (new ColumnFactory())->createColumn(type: 'inta', name: 'foo');
    }    
    
    public function testCreateColumnFromArrayMethodThrowsExceptionIfNoTypeSpecified()
    {
        $this->expectException(CreateColumnException::class);
        
        $column = (new ColumnFactory())->createColumnFromArray([]);
    }
    
    public function testCreateColumnFromArrayMethodThrowsExceptionIfNoNameSpecified()
    {
        $this->expectException(CreateColumnException::class);
        
        $column = (new ColumnFactory())->createColumnFromArray([
            'type' => 'int',
        ]);
    }
    
    public function testCreateColumnFromArrayMethodThrowsExceptionIfInvalidTypeSpecified()
    {
        $this->expectException(CreateColumnException::class);
        
        $column = (new ColumnFactory())->createColumnFromArray([
            'type' => 'inta',
            'name' => 'foo',
        ]);
    }
    
    public function testCreateColumnFromArrayMethodThrowsExceptionIfInvalidNameSpecified()
    {
        $this->expectException(CreateColumnException::class);
        
        $column = (new ColumnFactory())->createColumnFromArray([
            'type' => 'int',
            'name' => [],
        ]);
    }
    
    public function testCreateColumnFromArrayMethod()
    {
        $column = (new ColumnFactory())->createColumnFromArray([
            'type' => 'int',
            'name' => 'foo',
        ]);
        
        $this->assertInstanceOf(ColumnInterface::class, $column);
        $this->assertSame('int', $column->getType());
        $this->assertSame('foo', $column->getName());        
    }
    
    public function testCreateColumnFromArrayMethodLength()
    {
        $types = $this->types;
        unset($types['bool']);
        unset($types['decimal']);
        
        foreach($types as $type) {
            
            $column = (new ColumnFactory())->createColumnFromArray([
                'type' => $type,
                'name' => 'foo',
                'length' => 99,
            ]);
            
            if ($column instanceof Lengthable) {
                $this->assertSame(99, $column->getLength());
            }
        }
    }
    
    public function testCreateColumnFromArrayMethodNullable()
    {
        foreach($this->types as $type) {
            
            $column = (new ColumnFactory())->createColumnFromArray([
                'type' => $type,
                'name' => 'foo',
                'nullable' => false,
            ]);
            
            if ($column instanceof Nullable) {
                $this->assertFalse($column->isNullable());
            }
        }
    }
    
    public function testCreateColumnFromArrayMethodDefault()
    {
        foreach($this->types as $type) {
            
            $column = (new ColumnFactory())->createColumnFromArray([
                'type' => $type,
                'name' => 'foo',
                'default' => 'value',
            ]);
            
            if ($column instanceof Defaultable) {
                $this->assertTrue(!empty($column->getDefault()));
            }
        }
    }
    
    public function testCreateColumnFromArrayMethodUnsigned()
    {
        foreach($this->types as $type) {
            
            $column = (new ColumnFactory())->createColumnFromArray([
                'type' => $type,
                'name' => 'foo',
                'unsigned' => true,
            ]);
            
            if ($column instanceof Unsignable) {
                $this->assertTrue($column->isUnsigned());
            }
        }
    }
    
    public function testCreateColumnFromArrayMethodParameters()
    {
        foreach($this->types as $type) {
            
            $column = (new ColumnFactory())->createColumnFromArray([
                'type' => $type,
                'name' => 'foo',
                'parameters' => [
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_roman_ci',
                    // invalid should be ignored
                    45 => 'foo',
                ],
            ]);
            
            $this->assertSame('utf8mb4', $column->getParameter(name: 'charset'));
            $this->assertSame('utf8mb4_roman_ci', $column->getParameter(name: 'collation'));
        }
    }    
    
    public function testCreateColumnFromArrayMethodDecimalSpecific()
    {
        $column = (new ColumnFactory())->createColumnFromArray([
            'type' => 'decimal',
            'name' => 'foo',
            'precision' => 8,
            'scale' => 4,
        ]);

        $this->assertSame('8,4', $column->getLength());
    }
}