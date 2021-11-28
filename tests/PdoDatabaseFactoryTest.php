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
use Tobento\Service\Database\PdoDatabaseFactory;
use Tobento\Service\Database\DatabaseFactoryInterface;
use Tobento\Service\Database\PdoFactoryInterface;
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\DatabaseException;
use PDO;

/**
 * PdoDatabaseFactoryTest tests
 */
class PdoDatabaseFactoryTest extends TestCase
{
    public function testThatImplementsDatabaseFactoryInterface()
    {        
        $this->assertInstanceOf(
            DatabaseFactoryInterface::class,
            new PdoDatabaseFactory()
        );     
    }
    
    public function testThatImplementsPdoFactoryInterface()
    {        
        $this->assertInstanceOf(
            PdoFactoryInterface::class,
            new PdoDatabaseFactory()
        );     
    }    
    
    public function testCreateDatabaseMethod()
    {        
        $database = (new PdoDatabaseFactory())->createDatabase(
            name: 'sqlite',
            config: [
                'dsn' => 'sqlite::memory:',
            ],
        );
        
        $this->assertInstanceOf(
            DatabaseInterface::class,
            $database
        );
        
        $this->assertInstanceOf(
            PdoDatabaseInterface::class,
            $database
        );        
    }
    
    public function testCreateDatabaseMethodThrowsDatabaseExceptionOnFailure()
    {
        $this->expectException(DatabaseException::class);
        
        (new PdoDatabaseFactory())->createDatabase(
            name: 'sqlite',
            config: [
                'dsn' => 'sqlite::memory:invalid',
            ],
        );      
    }
    
    public function testCreatePdoMethod()
    {        
        $pdo = (new PdoDatabaseFactory())->createPdo(
            name: 'sqlite',
            config: [
                'dsn' => 'sqlite::memory:',
            ],
        );
        
        $this->assertInstanceOf(
            PDO::class,
            $pdo
        );       
    }
    
    public function testCreatePdoMethodThrowsDatabaseExceptionOnFailure()
    {
        $this->expectException(DatabaseException::class);
        
        (new PdoDatabaseFactory())->createPdo(
            name: 'sqlite',
            config: [
                'dsn' => 'sqlite::memory:invalid',
            ],
        );      
    }     
}