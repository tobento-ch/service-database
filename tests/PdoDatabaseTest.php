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
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\DatabaseException;
use PDOStatement;
use PDOException;
use Exception;
use PDO;

/**
 * PdoDatabaseTest tests
 */
class PdoDatabaseTest extends TestCase
{
    public function testThatImplementsDatabaseInterface()
    {
        $database = new PdoDatabase(
            pdo: new PDO('sqlite::memory:'),
            name: 'name',
        );
        
        $this->assertInstanceOf(
            DatabaseInterface::class,
            $database
        );     
    }
    
    public function testThatImplementsPdoDatabaseInterface()
    {
        $database = new PdoDatabase(
            pdo: new PDO('sqlite::memory:'),
            name: 'name',
        );
        
        $this->assertInstanceOf(
            PdoDatabaseInterface::class,
            $database
        );
    }    
    
    public function testExecuteMethodReturnsPdoStatement()
    {        
        $database = new PdoDatabase(new PDO('sqlite::memory:'));
        
        $statement = $database->execute(
            statement: 'CREATE TABLE IF NOT EXISTS products (name varchar(11))'
        );
        
        $this->assertInstanceOf(
            PDOStatement::class,
            $statement
        );
    }
    
    public function testExecuteMethodWithBindings()
    {        
        $database = new PdoDatabase(new PDO('sqlite::memory:'));

        $statement = $database->execute(
            statement: 'CREATE TABLE IF NOT EXISTS products (name varchar(11))'
        );
        
        $items = $database->execute(
            statement: 'SELECT * FROM products WHERE name = ?',
            bindings: ['red']
        )->fetchAll();
        
        $this->assertSame(
            [],
            $items
        );
    }
    
    public function testExecuteMethodWithNamedBindings()
    {        
        $database = new PdoDatabase(new PDO('sqlite::memory:'));

        $statement = $database->execute(
            statement: 'CREATE TABLE IF NOT EXISTS products (name varchar(11))'
        );
        
        $items = $database->execute(
            statement: 'SELECT * FROM products WHERE name = :name',
            bindings: ['name' => 'red']
        )->fetchAll();
        
        $this->assertSame(
            [],
            $items
        );
    }
    
    public function testTransactionMethod()
    {        
        $database = new PdoDatabase(new PDO('sqlite::memory:'));

        $database->transaction(function(PdoDatabaseInterface $db): void {

            $db->execute('CREATE TABLE IF NOT EXISTS products (name varchar(11))');
            
            $db->execute('CREATE TABLE IF NOT EXISTS articles (name varchar(11))');
        });

        $items = $database->execute(
            'SELECT * FROM products WHERE name = :name'
        )->fetchAll();
        
        $this->assertSame([], $items);
    }
    
    public function testTransactionMethodRollsbackOnFailure()
    {        
        $database = new PdoDatabase(new PDO('sqlite::memory:'));
        
        try {
            $database->transaction(function(PdoDatabaseInterface $db): void {

                $db->execute('CREATE TABLE IF NOT EXISTS products (name varchar(11))');

                throw new Exception('failing');
            });
        } catch (Exception $e) {
            //
        }

        try {
            $items = $database->execute(
                'SELECT * FROM products WHERE name = :name'
            )->fetchAll();
        } catch (PDOException $e) {
            $this->assertTrue(true);
        }
    }
    
    public function testPdoMethod()
    {
        $pdo = new PDO('sqlite::memory:');
        $database = new PdoDatabase($pdo);
        
        $this->assertSame(
            $pdo,
            $database->pdo()
        );
    }
    
    public function testConnectionMethod()
    {
        $pdo = new PDO('sqlite::memory:');
        $database = new PdoDatabase($pdo);
        
        $this->assertSame(
            $pdo,
            $database->connection()
        );
    }
    
    public function testParameterMethod()
    {
        $pdo = new PDO('sqlite::memory:');
        $database = new PdoDatabase($pdo, 'name', ['name' => 'value']);
        
        $this->assertSame(
            'value',
            $database->parameter('name')
        );
        
        $this->assertSame(
            null,
            $database->parameter('foo')
        );
        
        $this->assertSame(
            'default',
            $database->parameter('foo', 'default')
        );        
    }    
    
    public function testNameMethod()
    {
        $pdo = new PDO('sqlite::memory:');
        $database = new PdoDatabase($pdo, 'sql');
        
        $this->assertSame(
            'sql',
            $database->name()
        );
    }       
}