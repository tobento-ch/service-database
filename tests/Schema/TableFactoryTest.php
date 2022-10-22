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
use Tobento\Service\Database\Schema\TableFactoryInterface;
use Tobento\Service\Database\Schema\TableFactory;

/**
 * TableFactoryTest
 */
class TableFactoryTest extends TestCase
{
    public function testImplementsInterfaces()
    {
        $this->assertInstanceOf(TableFactoryInterface::class, new TableFactory());
    }
    
    public function testCreateTableMethod()
    {
        $table = (new TableFactory())->createTable(name: 'users');
        
        $this->assertSame('users', $table->getName());
    }
}