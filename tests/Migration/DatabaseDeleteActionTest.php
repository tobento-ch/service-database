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

namespace Tobento\Service\Database\Test\Migration;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Database\Migration\DatabaseAction;
use Tobento\Service\Database\Migration\DatabaseDeleteAction;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Database\Processor\Processors;
use Tobento\Service\Database\Processor\ProcessorInterface;
use Tobento\Service\Database\Processor\PdoMySqlProcessor;
use Tobento\Service\Database\Processor\PdoMySqlStorage;
use PDO;

/**
 * DatabaseDeleteActionTest
 */
class DatabaseDeleteActionTest extends TestCase
{
    public function testSpecificMethods()
    {
        $processor = new Processors();
        $database = new PdoDatabase(pdo: new PDO('sqlite::memory:'), name: 'name');
        $table = new Table(name: 'users');
        
        $action = new DatabaseDeleteAction(
            processor: $processor,
            database: $database,
            table: $table,
        );
        
        $this->assertInstanceof(ProcessorInterface::class, $action->processor());
        $this->assertTrue($processor === $action->processor());
        
        $this->assertInstanceof(DatabaseInterface::class, $action->database());
        $this->assertTrue($database === $action->database());
        
        $this->assertInstanceof(Table::class, $action->table());
        $this->assertTrue($table === $action->table());
    }
    
    public function testNameAndDescriptionMethods()
    {
        $action = new DatabaseDeleteAction(
            processor: new Processors(),
            database: new PdoDatabase(pdo: new PDO('sqlite::memory:'), name: 'name'),
            table: new Table(name: 'users'),
            name: 'Users',
            description: 'Users desc',
        );
        
        $this->assertSame('Users', $action->name());
        $this->assertSame('Users desc', $action->description());
    }
    
    public function testNameMethodReturnsTableNameIfNoneSpecified()
    {
        $action = new DatabaseDeleteAction(
            processor: new Processors(),
            database: new PdoDatabase(pdo: new PDO('sqlite::memory:'), name: 'name'),
            table: new Table(name: 'users'),
        );
        
        $this->assertSame('users', $action->name());
    }
    
    public function testSpecifyTableWithClosure()
    {
        $action = new DatabaseDeleteAction(
            processor: new Processors(),
            database: new PdoDatabase(pdo: new PDO('sqlite::memory:'), name: 'name'),
            table: function() {
                return new Table(name: 'users');
            },
        );
        
        $this->assertInstanceof(Table::class, $action->table());
    }
    
    public function testProcessMethod()
    {
        if (! getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL')) {
            $this->markTestSkipped('PdoMySqlProcessor tests are disabled');
        }

        $pdo = new PDO(
            dsn: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_DSN'),
            username: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_USERNAME'),
            password: getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL_PASSWORD'),
            options: [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
        
        $database = new PdoDatabase(pdo: $pdo, name: 'name');
        $processor = new PdoMySqlProcessor();
        $storage = new PdoMySqlStorage();
        
        $table = new Table(name: 'users');
        $table->primary('id');
        $table->string('name');
        
        // first create tables
        $action = new DatabaseAction(
            processor: $processor,
            database: $database,
            table: $table,
        );
        
        $action->process();
        
        $savedTable = $storage->fetchTable($database, $table->getName());
        
        $this->assertSame(2, count($savedTable->getColumns()));
        
        // Next delete action
        $action = new DatabaseDeleteAction(
            processor: $processor,
            database: $database,
            table: $table,
        );

        $action->process();
        
        $deletedTable = $storage->fetchTable($database, $table->getName());
        
        $this->assertSame(null, $deletedTable);
    }
}