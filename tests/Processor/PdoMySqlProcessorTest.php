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
use Tobento\Service\Database\Processor\PdoMySqlProcessor;
use Tobento\Service\Database\Processor\PdoMySqlStorage;
use Tobento\Service\Database\Processor\ProcessorInterface;
use Tobento\Service\Database\Processor\ProcessException;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Database\Schema\Items;
use PDO;

/**
 * PdoMySqlProcessorTest
 */
class PdoMySqlProcessorTest extends TestCase
{
    /**
     * @var null|PdoDatabaseInterface
     */
    protected null|PdoDatabaseInterface $database = null;
    
    protected function setUp(): void
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
        
        $this->database = new PdoDatabase(
            pdo: $pdo,
            name: 'name',
        );
    }
    
    public function testThatImplementsProcessorInterface()
    {
        $this->assertInstanceOf(
            ProcessorInterface::class,
            new PdoMySqlProcessor()
        );
    }
    
    public function testProcessNewTable()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(1, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        $this->assertSame('id', $savedTable->getColumns()['id']->getName());
        
        $this->dropTable($tableName);
    }
    
    public function testProcessNewTableMultipleColumns()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        $table->string('string')->length(21);
        $table->bool('bool')->default(true);
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(3, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $this->assertSame(
            ['id', 'string', 'bool'],
            array_keys($savedTable->getColumns())
        );
        
        $this->dropTable($tableName);
    }    
 
    public function testProcessNewAndModifyColumns()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        $table->string('string')->length(21);
        $table->bool('bool')->default(true);
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
        
        // new and modify columns
        $table = new Table(name: $tableName);
        $table->string('string')->length(21); // modify
        $table->float('float'); // new    
        
        $processor->process($table, $this->database);
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(4, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $this->assertSame(
            ['id', 'string', 'float', 'bool'],
            array_keys($savedTable->getColumns())
        );
        
        $this->dropTable($tableName);
    }
    
    public function testProcessWithIndexes()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        $table->int('int');
        $table->index('index_int')->column('int');
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
        
        // new and modify columns
        $table = new Table(name: $tableName);
        $table->bigInt('bigInt');
        $table->index('index_bigInt')->column('bigInt');
        
        $processor->process($table, $this->database);
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(3, count($savedTable->getColumns()));
        $this->assertSame(2, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $this->assertSame(
            ['id', 'int', 'bigInt'],
            array_keys($savedTable->getColumns())
        );
        
        $this->assertSame(
            ['index_int', 'index_bigInt'],
            array_keys($savedTable->getIndexes())
        );        
        
        $this->dropTable($tableName);
    }    
 
    public function testProcessWithItems()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        $table->string('name');
        $table->bool('active');

        $table->items(new Items([
            ['name' => 'foo', 'active' => true],
            ['name' => 'bar', 'active' => true],
        ]))
        ->chunk(length: 100)
        ->useTransaction(true) // default is true
        ->forceInsert(false); // default is false
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(3, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(2, $savedTable->getItemsCount());   
        
        $this->dropTable($tableName);
    }

    public function testProcessWithItemsThrowsProcessExceptionIfColumnDoesNotExist()
    {        
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        $table->string('foo');
        $table->bool('active');

        $table->items(new Items([
            ['name' => 'foo', 'active' => true],
            ['name' => 'bar', 'active' => true],
        ]))
        ->chunk(length: 100)
        ->useTransaction(true) // default is true
        ->forceInsert(false); // default is false
        
        $processor = new PdoMySqlProcessor();
        
        try {
            $processor->process($table, $this->database); 
        } catch (ProcessException $e) {
            $this->assertTrue(true);
        }
        
        $this->dropTable($tableName);
    }
    
    public function testProcessMultipleTables()
    {
        $tableName = 'products';
        $tableNameUsers = 'users';
        
        $this->dropTable($tableName);
        $this->dropTable($tableNameUsers);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        
        $tableUser = new Table(name: $tableNameUsers);
        $tableUser->primary('id');        
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
        $processor->process($tableUser, $this->database);
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(1, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        $this->assertSame('id', $savedTable->getColumns()['id']->getName());
        
        $savedTableUsers = $storage->fetchTable($this->database, $tableNameUsers);
        
        $this->assertSame(1, count($savedTableUsers->getColumns()));
        $this->assertSame(0, count($savedTableUsers->getIndexes()));
        $this->assertSame(null, $savedTableUsers->getItems());
        $this->assertSame(0, $savedTableUsers->getItemsCount());
        $this->assertSame('id', $savedTableUsers->getColumns()['id']->getName());        
        
        $this->dropTable($tableName);
        $this->dropTable($tableNameUsers);
    }    
    
    protected function dropTable(string $table): void
    {
        $table = new Table(name: $table);
        $table->dropTable();
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
    }  
}