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
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Database\Processor\Processors;
use Tobento\Service\Database\Processor\ProcessorInterface;
use Tobento\Service\Database\Processor\PdoMySqlProcessor;
use Tobento\Service\Database\Processor\PdoMySqlStorage;
use PDO;

/**
 * DatabaseActionTest
 */
class DatabaseActionTest extends TestCase
{
    public function testSpecificMethods()
    {
        $processor = new Processors();
        $database = new PdoDatabase(pdo: new PDO('sqlite::memory:'), name: 'name');
        $table = new Table(name: 'users');
        
        $action = new DatabaseAction(
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
        $action = new DatabaseAction(
            processor: new Processors(),
            database: new PdoDatabase(pdo: new PDO('sqlite::memory:'), name: 'name'),
            table: new Table(name: 'users'),
            name: 'Users',
            description: 'Users desc',
        );
        
        $this->assertSame('Users', $action->name());
        $this->assertSame('Users desc', $action->description());
        $this->assertSame('database', $action->type());
    }
    
    public function testNameMethodReturnsTableNameIfNoneSpecified()
    {
        $action = new DatabaseAction(
            processor: new Processors(),
            database: new PdoDatabase(pdo: new PDO('sqlite::memory:'), name: 'name'),
            table: new Table(name: 'users'),
        );
        
        $this->assertSame('users', $action->name());
    }
    
    public function testSpecifyTableWithClosure()
    {
        $action = new DatabaseAction(
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
        
        $table = new Table(name: 'users');
        $table->primary('id');
        $table->string('name');
        
        $action = new DatabaseAction(
            processor: $processor,
            database: $database,
            table: $table,
        );

        $action->process();
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($database, $table->getName());
        
        $this->assertSame(2, count($savedTable->getColumns()));
        
        // drop table
        $table->dropTable();
        
        $processor->process($table, $database);
    }
}