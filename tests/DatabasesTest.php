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
use Tobento\Service\Database\Databases;
use Tobento\Service\Database\DatabasesInterface;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\DatabaseException;
use PDO;

/**
 * DatabasesTest tests
 */
class DatabasesTest extends TestCase
{
    public function testThatImplementsDatabasesInterface()
    {
        $this->assertInstanceOf(
            DatabasesInterface::class,
            new Databases()
        );     
    }

    public function testConstructor()
    {
        $databases = new Databases(
            new PdoDatabase(
                pdo: new PDO('sqlite::memory:'),
                name: 'foo',
            ),
            new PdoDatabase(
                pdo: new PDO('sqlite::memory:'),
                name: 'bar',
            ),            
        );
            
        $this->assertTrue($databases->has('foo'));
        $this->assertTrue($databases->has('bar'));
    }
    
    public function testAddMethod()
    {
        $databases = new Databases();
        
        $database = new PdoDatabase(
            pdo: new PDO('sqlite::memory:'),
            name: 'name',
        );
        
        $databases->add($database);
            
        $this->assertTrue(true); 
    }
    
    public function testRegisterMethod()
    {
        $databases = new Databases();
        
        $databases->register(
            'name',
            function(string $name): DatabaseInterface {        
                return new PdoDatabase(
                    new PDO('sqlite::memory:'),
                    $name
                );
            }
        );
            
        $this->assertTrue(true); 
    }
    
    public function testGetMethod()
    {
        $databases = new Databases();
        
        $database = new PdoDatabase(
            pdo: new PDO('sqlite::memory:'),
            name: 'name',
        );
        
        $databases->add($database);
            
        $this->assertSame(
            $database,
            $databases->get('name')
        ); 
    }
    
    public function testGetMethodThrowsDatabaseExceptionIfNotExist()
    {
        $this->expectException(DatabaseException::class);
        
        $databases = new Databases();
        
        $databases->get('name'); 
    }
    
    public function testHasMethod()
    {
        $databases = new Databases();
        
        $database = new PdoDatabase(
            pdo: new PDO('sqlite::memory:'),
            name: 'name',
        );
        
        $databases->add($database);
            
        $this->assertTrue($databases->has('name'));
        
        $this->assertFalse($databases->has('foo')); 
    }
    
    public function testNamesMethod()
    {
        $databases = new Databases();
        
        $this->assertSame([], $databases->names());
        
        $databases->add(new PdoDatabase(
            pdo: new PDO('sqlite::memory:'),
            name: 'foo',
        ));
        
        $databases->register(
            'bar',
            function(string $name): DatabaseInterface {        
                return new PdoDatabase(
                    new PDO('sqlite::memory:'),
                    $name
                );
            }
        );
            
        $this->assertSame(['foo', 'bar'], $databases->names());
    }
    
    public function testAddDefaultMethod()
    {
        $databases = new Databases();
        
        $databases->addDefault(name: 'primary', database: 'sqlite');
            
        $this->assertTrue(true);
    }
    
    public function testDefaultMethod()
    {
        $databases = new Databases();

        $database = new PdoDatabase(
            pdo: new PDO('sqlite::memory:'),
            name: 'sqlite',
        );
        
        $databases->add($database);
        
        $databases->addDefault(name: 'primary', database: 'sqlite');
            
        $this->assertSame(
            $database,
            $databases->default('primary')
        );
    } 
    
    public function testDefaultMethodThrowsDatabaseExceptionIfNotExist()
    {
        $this->expectException(DatabaseException::class);
        
        $databases = new Databases();

        $databases->default('primary');
    }
    
    public function testHasDefaultMethod()
    {
        $databases = new Databases();
        
        $database = new PdoDatabase(
            pdo: new PDO('sqlite::memory:'),
            name: 'sqlite',
        );
        
        $databases->add($database);
        
        $databases->addDefault(name: 'primary', database: 'sqlite');
        
        $this->assertTrue($databases->hasDefault('primary'));
        
        $this->assertFalse($databases->hasDefault('foo')); 
    }
    
    public function testGetDefaultsMethod()
    {
        $databases = new Databases();
        
        $databases->addDefault(name: 'primary', database: 'sqlite');
        $databases->addDefault(name: 'secondary', database: 'pdo');
        
        $this->assertSame(
            [
                'primary' => 'sqlite',
                'secondary' => 'pdo',
            ],
            $databases->getDefaults()
        );
    }    
}