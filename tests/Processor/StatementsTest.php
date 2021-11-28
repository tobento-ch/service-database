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

namespace Tobento\Service\Database\Test\Processor;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Database\Processor\Statements;
use Tobento\Service\Database\Processor\Statement;
use Tobento\Service\Database\Schema\Table;
use IteratorAggregate;

/**
 * StatementsTest
 */
class StatementsTest extends TestCase
{
    public function testImplementsIteratorAggregate()
    {
        $statements = new Statements([], new Table('name'));
        
        $this->assertInstanceOf(IteratorAggregate::class, $statements);
    }
    
    public function testGetStatements()
    {
        $statement = new Statement(
            statement: 'Statement',
            bindings: ['foo' => 'bar'],
            transactionable: true
        );
        
        $statements = new Statements([$statement], new Table('name'));
        
        $this->assertTrue($statement === $statements->getStatements()[0]);
    }
    
    public function testGetTable()
    {
        $statement = new Statement(
            statement: 'Statement',
            bindings: ['foo' => 'bar'],
            transactionable: true
        );
        
        $table = new Table('name');
        
        $statements = new Statements([$statement], $table);
        
        $this->assertTrue($table === $statements->getTable());
    }    
}