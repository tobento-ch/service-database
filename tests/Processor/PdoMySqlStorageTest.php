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
use Tobento\Service\Database\Processor\StorageInterface;
use Tobento\Service\Database\Processor\StorageFetchException;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Database\Schema\Items;
use PDO;

/**
 * PdoMySqlStorageTest
 */
class PdoMySqlStorageTest extends TestCase
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
    
    public function testThatImplementsStorageInterface()
    {
        $this->assertInstanceOf(
            StorageInterface::class,
            new PdoMySqlStorage()
        );
    }
    
    public function testColumnsMapping()
    {
        $types = [
            'primary',
            //'bigPrimary',
            'bool',
            'int',
            'tinyInt',
            'bigInt',
            'char',
            'string',
            'text',
            'double',
            'float',
            'decimal',
            'datetime',
            'date',
            'time',
            'timestamp',
            'json',
        ];
        
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        
        foreach($types as $column) {
            $table->$column($column);
        }
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(16, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $savedTypes = [];
        
        foreach($savedTable->getColumns() as $savedColumn) {
            $savedTypes[] = $savedColumn->getType();
        }
        
        $this->assertSame($types, $savedTypes);
        
        $this->dropTable($tableName);
    }
    
    public function testColumnsMappingBigPrimary()
    {
        $types = [
            'bigPrimary',
        ];
        
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        
        foreach($types as $column) {
            $table->$column($column);
        }
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(1, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $savedTypes = [];
        
        foreach($savedTable->getColumns() as $savedColumn) {
            $savedTypes[] = $savedColumn->getType();
        }
        
        $this->assertSame($types, $savedTypes);
        
        $this->dropTable($tableName);
    }
    
    public function testSimpleIndex()
    {        
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->int('foo');
        $table->index('index_name')->column('foo');
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(1, count($savedTable->getColumns()));
        $this->assertSame(1, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $index = $savedTable->getIndexes()['index_name'];
        
        $this->assertSame('index_name', $index->getName());
        
        $this->assertSame(['foo'], $index->getColumns());
        
        $this->assertFalse($index->isUnique());
        
        $this->assertFalse($index->isPrimary());
        
        $this->assertSame(null, $index->getRename());
        
        $this->assertFalse($index->dropping()); 
        
        $this->dropTable($tableName);
    }
    
    public function testCompoundIndex()
    {        
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->int('foo');
        $table->int('bar');
        $table->index('index_name')->column('foo', 'bar');
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(2, count($savedTable->getColumns()));
        $this->assertSame(1, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $index = $savedTable->getIndexes()['index_name'];
        
        $this->assertSame('index_name', $index->getName());
        
        $this->assertSame(['foo', 'bar'], $index->getColumns());
        
        $this->assertFalse($index->isUnique());
        
        $this->assertFalse($index->isPrimary());
        
        $this->assertSame(null, $index->getRename());
        
        $this->assertFalse($index->dropping()); 
        
        $this->dropTable($tableName);
    }    

    public function testSimpleUniqueIndex()
    {        
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->int('foo');
        $table->index('index_name')->column('foo')->unique();
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(1, count($savedTable->getColumns()));
        $this->assertSame(1, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $index = $savedTable->getIndexes()['index_name'];
        
        $this->assertSame('index_name', $index->getName());
        
        $this->assertSame(['foo'], $index->getColumns());
        
        $this->assertTrue($index->isUnique());
        
        $this->assertFalse($index->isPrimary());
        
        $this->assertSame(null, $index->getRename());
        
        $this->assertFalse($index->dropping()); 
        
        $this->dropTable($tableName);
    }

    public function testCompoundUniqueIndex()
    {        
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->int('foo');
        $table->int('bar');
        $table->index('index_name')->column('foo', 'bar')->unique();
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(2, count($savedTable->getColumns()));
        $this->assertSame(1, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $index = $savedTable->getIndexes()['index_name'];
        
        $this->assertSame('index_name', $index->getName());
        
        $this->assertSame(['foo', 'bar'], $index->getColumns());
        
        $this->assertTrue($index->isUnique());
        
        $this->assertFalse($index->isPrimary());
        
        $this->assertSame(null, $index->getRename());
        
        $this->assertFalse($index->dropping()); 
        
        $this->dropTable($tableName);
    }

    public function testPrimaryIndex()
    {        
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->int('foo');
        $table->index()->column('foo')->primary();
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoMySqlStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(1, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $column = $savedTable->getColumns()['foo'];
        
        $this->assertSame('primary', $column->getType());
        
        $this->dropTable($tableName);
    }

    public function testItemsCount()
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
        
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(2, $savedTable->getItemsCount());
        
        $this->dropTable($tableName);
    }
    
    protected function dropTable(string $table): void
    {
        $table = new Table(name: $table);
        $table->dropTable();
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
    }  
}