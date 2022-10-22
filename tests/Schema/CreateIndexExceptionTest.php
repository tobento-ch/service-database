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
use Tobento\Service\Database\Schema\CreateIndexException;

/**
 * CreateIndexExceptionTest
 */
class CreateIndexExceptionTest extends TestCase
{
    public function testIndexMethod()
    {
        $e = new CreateIndexException(
            ['name' => 'foo'],
            'Message'
        );
        
        $this->assertSame(['name' => 'foo'], $e->index());
    }
    
    public function testNameMethod()
    {
        $e = new CreateIndexException(
            ['name' => 'foo'],
            'Message'
        );
        
        $this->assertSame('foo', $e->name());
    }
    
    public function testNameMethodReturnsEmptyStringIfInvalid()
    {
        $e = new CreateIndexException(['name' => []]);
        
        $this->assertSame('', $e->name());
    }    
    
    public function testWithNoMessage()
    {
        $e = new CreateIndexException(
            ['name' => 'foo'],
        );
        
        $this->assertSame('Creating index with name [foo] failed!', $e->getMessage());
    }    
}