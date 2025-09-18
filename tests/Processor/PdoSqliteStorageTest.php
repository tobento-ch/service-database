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
use Tobento\Service\Database\Processor\StorageInterface;
use Tobento\Service\Database\Processor\StorageFetchException;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Database\Schema\Items;
use PDO;

class PdoSqliteStorageTest extends TestCase
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
    
    public function testThatImplementsStorageInterface()
    {
        $this->assertInstanceOf(
            StorageInterface::class,
            new PdoSqliteStorage()
        );
    }
    
    public function testColumnsMapping()
    {
        $types = [
            'primary' => 'primary',
            'bool' => 'int',
            'int' => 'int',
            'tinyInt' => 'int',
            'bigInt' => 'int',
            'char' => 'char',
            'string' => 'string',
            'text' => 'text',
            'blob' => 'blob',
            'double' => 'double',
            'float' => 'float',
            'decimal' => 'decimal',
            'datetime' => 'datetime',
            'date' => 'date',
            'time' => 'time',
            'timestamp' => 'timestamp',
            'json' => 'text',
        ];
        
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        
        foreach(array_keys($types) as $column) {
            $table->$column($column);
        }
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(17, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $savedTypes = [];
        
        foreach($savedTable->getColumns() as $savedColumn) {
            $savedTypes[] = $savedColumn->getType();
        }
        
        $this->assertSame(array_values($types), $savedTypes);
        
        $this->dropTable($tableName);
    }
    
    public function testSimpleIndex()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->int('foo');
        $table->index('index_name')->column('foo');
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(1, count($savedTable->getColumns()));
        $this->assertSame(1, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $index = $savedTable->getIndexes()['products_index_name'];
        
        $this->assertSame('products_index_name', $index->getName());
        
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
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(2, count($savedTable->getColumns()));
        $this->assertSame(1, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $index = $savedTable->getIndexes()['products_index_name'];
        
        $this->assertSame('products_index_name', $index->getName());
        
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
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(1, count($savedTable->getColumns()));
        $this->assertSame(1, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $index = $savedTable->getIndexes()['products_index_name'];
        
        $this->assertSame('products_index_name', $index->getName());
        
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
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(2, count($savedTable->getColumns()));
        $this->assertSame(1, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $index = $savedTable->getIndexes()['products_index_name'];
        
        $this->assertSame('products_index_name', $index->getName());
        
        $this->assertSame(['foo', 'bar'], $index->getColumns());
        
        $this->assertTrue($index->isUnique());
        
        $this->assertFalse($index->isPrimary());
        
        $this->assertSame(null, $index->getRename());
        
        $this->assertFalse($index->dropping()); 
        
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
        
        $processor = new PdoSqliteProcessor();
        
        $processor->process($table, $this->database);
        
        $storage = new PdoSqliteStorage();
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(2, $savedTable->getItemsCount());
        
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