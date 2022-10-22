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
use Tobento\Service\Database\Schema\CreateColumnException;

/**
 * CreateColumnExceptionTest
 */
class CreateColumnExceptionTest extends TestCase
{
    public function testColumnMethod()
    {
        $e = new CreateColumnException(
            ['type' => 'int'],
            'Invalid column type'
        );
        
        $this->assertSame(['type' => 'int'], $e->column());
    }
    
    public function testTypeMethod()
    {
        $e = new CreateColumnException(
            ['type' => 'int'],
            'Invalid column type'
        );
        
        $this->assertSame('int', $e->type());
    }
    
    public function testTypeMethodReturnsEmptyStringIfInvalid()
    {
        $e = new CreateColumnException(['type' => []]);
        
        $this->assertSame('', $e->type());
    }
    
    public function testNameMethod()
    {
        $e = new CreateColumnException(
            ['name' => 'foo'],
            'Invalid column type'
        );
        
        $this->assertSame('foo', $e->name());
    }
    
    public function testNameMethodReturnsEmptyStringIfInvalid()
    {
        $e = new CreateColumnException(['name' => []]);
        
        $this->assertSame('', $e->name());
    }    
    
    public function testWithNoMessage()
    {
        $e = new CreateColumnException(
            ['type' => 'int', 'name' => 'foo'],
        );
        
        $this->assertSame('Creating column of name [foo] and type [int] failed!', $e->getMessage());
    }    
}