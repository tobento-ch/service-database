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
use Tobento\Service\Database\Processor\Statement;

/**
 * StatementTest
 */
class StatementTest extends TestCase
{    
    public function testMethods()
    {
        $statement = new Statement(
            statement: 'Statement',
            bindings: ['foo' => 'bar'],
            transactionable: true
        );
        
        $this->assertSame('Statement', $statement->getStatement());
        $this->assertSame(['foo' => 'bar'], $statement->getBindings());
        $this->assertTrue($statement->isTransactionable());
    }
}