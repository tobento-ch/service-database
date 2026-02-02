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
use Tobento\Service\Database\Processor\PdoSqliteProcessor;
use Tobento\Service\Database\Processor\PdoSqliteStorage;
use Tobento\Service\Database\Processor\ProcessorInterface;
use Tobento\Service\Database\Processor\ProcessException;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Iterable\ItemFactoryIterator;
use Tobento\Service\Iterable\JsonFileIterator;
use Tobento\Service\Iterable\ModifyIterator;
use PDO;

class PdoSqliteProcessorTest extends TestCase
{
    /**
     * @var null|PdoDatabaseInterface
     */
    protected null|PdoDatabaseInterface $database = null;
    
    protected function setUp(): void
    {
        $pdo = new PDO(
            dsn: 'sqlite::memory:',
            options: [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
        
        $this->database = new PdoDatabase(pdo: $pdo, name: 'name');
    }
    
    public function testThatImplementsProcessorInterface()
    {
        $this->assertInstanceOf(
            ProcessorInterface::class,
            new PdoSqliteProcessor()
        );
    }
    
    public function testProcessNewTable()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
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
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
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
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        // new and modify columns
        $table = new Table(name: $tableName);
        $table->string('string')->length(21); // modify
        $table->float('float'); // new    
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(4, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $this->assertSame(
            ['id', 'string', 'bool', 'float'],
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
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        // new and modify columns
        $table = new Table(name: $tableName);
        $table->bigInt('bigInt');
        $table->index('index_bigInt')->column('bigInt');
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
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
            ['products_index_bigInt', 'products_index_int'],
            array_keys($savedTable->getIndexes())
        );        
        
        $this->dropTable($tableName);
    }
    
    public function testProcessWithSameNameTableIndexes()
    {
        $this->dropTable('products');
        $this->dropTable('articles');
        
        $table = new Table(name: 'products');
        $table->int('int');
        $table->index('index_int')->column('int');
        
        $tableA = new Table(name: 'articles');
        $tableA->int('int');
        $tableA->index('index_int')->column('int');        
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        $processor->process($tableA, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, 'products');
        $savedTableA = $storage->fetchTable($this->database, 'articles');
        
        $this->assertSame(['products_index_int'], array_keys($savedTable->getIndexes()));
        $this->assertSame(['articles_index_int'], array_keys($savedTableA->getIndexes()));
        
        $this->dropTable('products');
        $this->dropTable('articles');
    }

    public function testProcessCanRenameIndex()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->int('foo');
        $table->index('index_foo')->column('foo');
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $table = new Table(name: $tableName);
        $table->index('index_foo')->rename('index_bar');
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(1, count($savedTable->getColumns()));
        $this->assertSame(1, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());

        $this->assertSame(
            ['products_index_bar'],
            array_keys($savedTable->getIndexes())
        );
        
        $this->dropTable($tableName);
    }
    
    public function testProcessCanDropIndex()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->int('foo');
        $table->index('index_foo')->column('foo');
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $table = new Table(name: $tableName);
        $table->index('index_foo')->drop();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(1, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());

        $this->dropTable($tableName);
    }
    
    public function testProcessCanAddPrimaryIndex()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        $table->index('index_int')->column('id');
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(1, count($savedTable->getColumns()));
        $this->assertSame(1, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $this->assertSame(
            ['products_index_int'],
            array_keys($savedTable->getIndexes())
        );
        
        $this->dropTable($tableName);
    }    

    public function testProcessCanRenameColumn()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        $table->string('name');
        $table->string('group'); // test for reserved keyword
        $table->bool('active');
        $table->index('index_name')->column('name');
        $table->items([
            ['name' => 'foo', 'active' => true, 'group' => 'foo'],
            ['name' => 'bar', 'active' => true, 'group' => 'bar'],
        ])->chunk(length: 100)->useTransaction(true)->forceInsert(false);        
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        // new and modify columns
        $table = new Table(name: $tableName);
        $table->renameColumn('name', 'new_name');
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(['id', 'group', 'active', 'new_name'], array_keys($savedTable->getColumns()));
        $this->assertSame(1, count($savedTable->getIndexes()));
        $this->assertSame(['new_name'], $savedTable->getIndex('products_index_name')->getColumns());
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(2, $savedTable->getItemsCount());    
        
        $this->dropTable($tableName);
    }
    
    public function testProcessCanDropColumn()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        $table->string('name');
        $table->bool('active');
        $table->items([
            ['name' => 'foo', 'active' => true],
            ['name' => 'bar', 'active' => true],
        ])->chunk(length: 100)->useTransaction(true)->forceInsert(false);        
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        // new and modify columns
        $table = new Table(name: $tableName);
        $table->dropColumn('active');
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(2, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(2, $savedTable->getItemsCount());    
        
        $this->dropTable($tableName);
    }
    
    public function testProcessCanRemovePrimaryColumn()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('foo');
        $table->int('int');
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
                
        // new and modify columns
        $table = new Table(name: $tableName);
        $table->dropColumn('foo');
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(1, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());    
        
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

        $table->items([
            ['name' => 'foo', 'active' => true],
            ['name' => 'bar', 'active' => true],
        ])
        ->chunk(length: 100)
        ->useTransaction(true) // default is true
        ->forceInsert(false); // default is false
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(3, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(2, $savedTable->getItemsCount());   
        
        $this->dropTable($tableName);
    }
    
    public function testProcessWithItemFactory()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        $table->string('name');
        $table->bool('active');

        $table->items(new ItemFactoryIterator(
            factory: function(): array {
                return [
                    'name' => 'pen',
                    'active' => true,
                ];
            },
            create: 2
        ))
        ->chunk(length: 100)
        ->useTransaction(true) // default is true
        ->forceInsert(false); // default is false
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(3, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(2, $savedTable->getItemsCount());   
        
        $this->dropTable($tableName);
    }
    
    public function testProcessWithJsonFileItemsAndModifyIterator()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        $table->string('iso');
        $table->string('name');

        $iterator = new JsonFileIterator(
            file: __DIR__.'/../src/countries.json',
        );
        
        $iterator = new ModifyIterator(
            iterable: $iterator,
            modifier: function(array $item): array {
                return [
                  'iso' => $item['iso'] ?? '',
                  'name' => $item['country'] ?? '',
                ];
            }
        );
        
        $table->items($iterator)
        ->chunk(length: 100)
        ->useTransaction(true) // default is true
        ->forceInsert(false); // default is false
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(3, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(3, $savedTable->getItemsCount());   
        
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

        $table->items([
            ['name' => 'foo', 'active' => true],
            ['name' => 'bar', 'active' => true],
        ])
        ->chunk(length: 100)
        ->useTransaction(true) // default is true
        ->forceInsert(false); // default is false
        
        $processor = new PdoSqliteProcessor();
        
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
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        $processor->process($tableUser, $this->database);
        
        $storage = new PdoSqliteStorage();
        
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
    
    public function testCanTruncateTable()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        $table->string('name');
        $table->bool('active');

        $table->items([
            ['name' => 'foo', 'active' => true],
            ['name' => 'bar', 'active' => true],
        ])
        ->chunk(length: 100)
        ->useTransaction(true) // default is true
        ->forceInsert(false); // default is false
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(2, $savedTable->getItemsCount());
        
        $table = new Table(name: $tableName);
        $table->truncate();
        
        $processor->process($table, $this->database);
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $this->dropTable($tableName);
    }
    
    public function testCanRenameTable()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        $table->string('name');
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $table = new Table(name: $tableName);
        $table->renameTable('newname');
        $tableName = 'newname';
        
        $processor->process($table, $this->database);
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame('newname', $savedTable->getName());
        
        $this->dropTable($tableName);
    }
    
    protected function dropTable(string $table): void
    {
        $table = new Table(name: $table);
        $table->dropTable();
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
    }  
}