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
use Tobento\Service\Database\Processor\Storages;
use Tobento\Service\Database\Processor\PdoMySqlStorage;
use Tobento\Service\Database\Processor\StorageInterface;
use Tobento\Service\Database\Processor\StorageFetchException;
use Tobento\Service\Database\Processor\StorageStoreException;
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Database\Processor\PdoMySqlProcessor;
use Tobento\Service\Database\PdoDatabase;
use PDO;

/**
 * StoragesTest
 */
class StoragesTest extends TestCase
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
            new Storages()
        );
    }
    
    public function testFetch()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);        
        
        $storage = new Storages(
            new PdoMySqlStorage(),
        );
        
        $savedTable = $storage->fetchTable($this->database, $tableName);
        
        $this->assertSame(1, count($savedTable->getColumns()));
        $this->assertSame(0, count($savedTable->getIndexes()));
        $this->assertSame(null, $savedTable->getItems());
        $this->assertSame(0, $savedTable->getItemsCount());
        
        $this->dropTable($tableName);
    }
    
    public function testFetchThrowsStorageFetchExceptionIfNoStorageSupportsDatabase()
    {
        $tableName = 'products';
        
        $table = new Table(name: $tableName);
        $table->primary('id');

        $storage = new Storages();
        
        $this->assertFalse($storage->supportsDatabase($this->database));
        
        try {
            $storage->fetchTable($this->database, $tableName);  
        } catch (StorageFetchException $e) {
            $this->assertTrue(true);
        }
    }    
 
    public function testStore()
    {
        $tableName = 'products';
        
        $this->dropTable($tableName);
        
        $table = new Table(name: $tableName);
        $table->primary('id');
        
        $storage = new Storages(
            new PdoMySqlStorage(),
        );
        
        $storage->storeTable($this->database, $table);
        
        $this->assertTrue(true);
        
        $this->dropTable($tableName);
    }
    
    public function testStoreThrowsStorageStoreExceptionIfNoStorageSupportsDatabase()
    {
        $tableName = 'products';
        
        $table = new Table(name: $tableName);
        $table->primary('id');

        $storage = new Storages();
        
        $this->assertFalse($storage->supportsDatabase($this->database));
        
        try {
            $storage->storeTable($this->database, $table);  
        } catch (StorageStoreException $e) {
            $this->assertTrue(true);
        }
    } 
    
    protected function dropTable(string $table): void
    {
        $table = new Table(name: $table);
        $table->dropTable();
        
        $processor = new PdoMySqlProcessor();
        
        $processor->process($table, $this->database);
    }     
}