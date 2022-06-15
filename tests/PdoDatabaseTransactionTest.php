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

namespace Tobento\Service\Database\Test;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\Processor\PdoMySqlProcessor;
use Tobento\Service\Database\Schema\Table;
use Error;
use PDO;

/**
 * PdoDatabaseTransactionTest
 */
class PdoDatabaseTransactionTest extends TestCase
{
    protected null|PdoDatabase $database = null;    
    protected null|Table $tableProducts = null;
    
    protected function setUp(): void
    {
        if (! getenv('TEST_TOBENTO_DATABASE_PDO_MYSQL')) {
            $this->markTestSkipped('PdoDatabaseTransaction tests are disabled');
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
        
        $tableProducts = new Table(name: 'products');
        $tableProducts->bigPrimary('id');
        $tableProducts->string('sku', 100)->nullable(false)->default('');
        $this->tableProducts = $tableProducts;
        
        $processor = new PdoMySqlProcessor();
        $processor->process($this->tableProducts, $this->database);        
    }
    
    public function testCommitInsert()
    {
        $this->database->begin();

        $this->database->execute(
            statement: 'INSERT INTO products (sku) VALUES (?)',
            bindings: ['green'],
        );
        
        $this->assertSame(1, $this->getProductsCount());
        
        $this->database->commit();

        $this->assertSame(1, $this->getProductsCount());
    }
    
    public function testCommitInsertClosure()
    {
        $this->database->transaction(function($database) {
            $database->execute(
                statement: 'INSERT INTO products (sku) VALUES (?)',
                bindings: ['green'],
            );

            $this->assertSame(1, $this->getProductsCount());
        });

        $this->assertSame(1, $this->getProductsCount());
    }
    
    public function testRollbackInsert()
    {
        $this->database->begin();

        $this->database->execute(
            statement: 'INSERT INTO products (sku) VALUES (?)',
            bindings: ['green'],
        );

        $this->assertSame(1, $this->getProductsCount());
        
        $this->database->rollback();
        
        $this->assertSame(0, $this->getProductsCount());
    }    
    
    public function testRollbackInsertClosure()
    {
        try {
            $this->database->transaction(function($database) {
                $this->database->execute(
                    statement: 'INSERT INTO products (sku) VALUES (?)',
                    bindings: ['green'],
                );
                
                $this->assertSame(1, $this->getProductsCount());

                throw new Error('Something went wrong');
            });
        } catch (Error $e) {
            $this->assertSame('Something went wrong', $e->getMessage());
        }

        $this->assertSame(0, $this->getProductsCount());
    }
    
    public function testRollbackInsertClosureNested()
    {
        try {
            $this->database->transaction(function($database) {

                $database->transaction(function($database) {

                    $this->database->execute(
                        statement: 'INSERT INTO products (sku) VALUES (?)',
                        bindings: ['green'],
                    );
                    
                    $this->assertSame(1, $this->getProductsCount());
                });
                
                $database->execute(
                    statement: 'INSERT INTO products (sku) VALUES (?)',
                    bindings: ['green'],
                );             
                
                $this->assertSame(2, $this->getProductsCount());
                
                throw new Error('Something went wrong');
            });
        } catch (Error $e) {
            $this->assertSame('Something went wrong', $e->getMessage());
        }

        $this->assertSame(0, $this->getProductsCount());
    }
    
    public function testCommitInsertNested()
    {
        $this->database->begin();

        $this->database->execute(
            statement: 'INSERT INTO products (sku) VALUES (?)',
            bindings: ['green'],
        );

        $this->assertSame(1, $this->getProductsCount());
        
        $this->database->begin();
        
        $this->database->execute(
            statement: 'INSERT INTO products (sku) VALUES (?)',
            bindings: ['red'],
        );       
        
        $this->assertSame(
            ['green', 'red'],
            $this->getProductsSku()
        );
        
        $this->database->commit();
        
        $this->assertSame(
            ['green', 'red'],
            $this->getProductsSku()
        );
        
        $this->database->commit();
        
        $this->assertSame(
            ['green', 'red'],
            $this->getProductsSku()
        );       
    }
    
    public function testRollbackInsertNested()
    {
        $this->database->begin();

        $this->database->execute(
            statement: 'INSERT INTO products (sku) VALUES (?)',
            bindings: ['green'],
        );

        $this->assertSame(1, $this->getProductsCount());
        
        $this->database->begin();
        
        $this->database->execute(
            statement: 'INSERT INTO products (sku) VALUES (?)',
            bindings: ['red'],
        );
        
        $this->assertSame(
            ['green', 'red'],
            $this->getProductsSku()
        );
        
        $this->database->rollback();
        
        $this->assertSame(
            ['green'],
            $this->getProductsSku()
        );
        
        $this->database->commit();
        
        $this->assertSame(
            ['green'],
            $this->getProductsSku()
        );   
    }
    
    protected function getProductsCount(): int
    {
        return $this->database->execute('SELECT COUNT(*) FROM products')->fetchColumn();
    }
    
    protected function getProductsSku(): array
    {
        return array_values($this->database->execute('SELECT id, sku FROM products')
            ->fetchAll(\PDO::FETCH_KEY_PAIR));
    }    
    
    public function tearDown(): void
    {
        $this->dropTable($this->tableProducts);
    }
    
    protected function dropTable(Table $table): void
    {
        $table->dropTable();
        $processor = new PdoMySqlProcessor();
        $processor->process($table, $this->database);
    }
}