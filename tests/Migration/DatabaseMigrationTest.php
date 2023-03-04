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
use Tobento\Service\Database\Migration\DatabaseMigration;
use Tobento\Service\Database\Migration\DatabaseAction;
use Tobento\Service\Database\Migration\DatabaseDeleteAction;
use Tobento\Service\Database\Databases;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\Processor\Processors;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Migration\ActionsInterface;
use Tobento\Service\Migration\Actions;
use PDO;

/**
 * DatabaseMigrationTest
 */
class DatabaseMigrationTest extends TestCase
{
    public function testInstallMethodReturnsEmptyActionIfNoneSpecified()
    {
        $processors = new Processors();
        $databases = new Databases();
        
        $migration = new class($processors, $databases) extends DatabaseMigration
        {
            public function description(): string
            {
                return 'db migration';
            }
        };
        
        $actions = $migration->install();
        
        $this->assertTrue($actions->empty());
    }

    public function testInstallMethodWithRegisterTables()
    {
        $processors = new Processors();
        $databases = new Databases();
        $databases->add(new PdoDatabase(pdo: new PDO('sqlite::memory:'), name: 'memory'));
        
        $migration = new class($processors, $databases) extends DatabaseMigration
        {
            public function description(): string
            {
                return 'db migration';
            }
            
            protected function registerTables(): void
            {
                $table = new Table(name: 'products');
                $table->primary('id');
                
                $this->registerTable(
                    table: $table,
                    database: $this->databases->get('memory'),
                    name: 'Products',
                    description: 'Products desc',
                );
            }
        };
        
        $actions = $migration->install();
        $action = $actions->first();
        
        $this->assertSame('Products', $action?->name());
        $this->assertSame('products', $action?->table()->getName());
        $this->assertTrue($databases->get('memory') === $action?->database());
        $this->assertTrue($processors === $action?->processor());
    }
    
    public function testInstallMethodWithRegisterTablesWithTableAsClosure()
    {
        $processors = new Processors();
        $databases = new Databases();
        $databases->add(new PdoDatabase(pdo: new PDO('sqlite::memory:'), name: 'memory'));
        
        $migration = new class($processors, $databases) extends DatabaseMigration
        {
            public function description(): string
            {
                return 'db migration';
            }
            
            protected function registerTables(): void
            {
                $this->registerTable(
                    table: function(): Table {
                        $table = new Table(name: 'products');
                        $table->primary('id');
                        return $table;
                    },
                    database: $this->databases->get('memory'),
                    name: 'Products',
                    description: 'Products desc',
                );
            }
        };
        
        $actions = $migration->install();
        $action = $actions->first();
        
        $this->assertSame('Products', $action?->name());
        $this->assertSame('products', $action?->table()->getName());
        $this->assertTrue($databases->get('memory') === $action?->database());
        $this->assertTrue($processors === $action?->processor());
    }
    
    public function testUninstallMethodReturnsEmptyActionIfNoneSpecified()
    {
        $processors = new Processors();
        $databases = new Databases();
        
        $migration = new class($processors, $databases) extends DatabaseMigration
        {
            public function description(): string
            {
                return 'db migration';
            }
        };
        
        $actions = $migration->uninstall();
        
        $this->assertTrue($actions->empty());
    }

    public function testUninstallMethodWithRegisterTables()
    {
        $processors = new Processors();
        $databases = new Databases();
        $databases->add(new PdoDatabase(pdo: new PDO('sqlite::memory:'), name: 'memory'));
        
        $migration = new class($processors, $databases) extends DatabaseMigration
        {
            public function description(): string
            {
                return 'db migration';
            }
            
            protected function registerTables(): void
            {
                $table = new Table(name: 'products');
                $table->primary('id');
                
                $this->registerTable(
                    table: $table,
                    database: $this->databases->get('memory'),
                    name: 'Products',
                    description: 'Products desc',
                );
            }
        };
        
        $actions = $migration->uninstall();
        $action = $actions->first();
        
        $this->assertSame('Products', $action?->name());
        $this->assertSame('products', $action?->table()->getName());
        $this->assertTrue($databases->get('memory') === $action?->database());
        $this->assertTrue($processors === $action?->processor());
    }
    
    public function testUninstallMethodWithRegisterTablesWithTableAsClosure()
    {
        $processors = new Processors();
        $databases = new Databases();
        $databases->add(new PdoDatabase(pdo: new PDO('sqlite::memory:'), name: 'memory'));
        
        $migration = new class($processors, $databases) extends DatabaseMigration
        {
            public function description(): string
            {
                return 'db migration';
            }
            
            protected function registerTables(): void
            {
                $this->registerTable(
                    table: function(): Table {
                        $table = new Table(name: 'products');
                        $table->primary('id');
                        return $table;
                    },
                    database: $this->databases->get('memory'),
                    name: 'Products',
                    description: 'Products desc',
                );
            }
        };
        
        $actions = $migration->uninstall();
        $action = $actions->first();
        
        $this->assertSame('Products', $action?->name());
        $this->assertSame('products', $action?->table()->getName());
        $this->assertTrue($databases->get('memory') === $action?->database());
        $this->assertTrue($processors === $action?->processor());
    }
    
    public function testUninstallMethodCreatedFromInstallActions()
    {
        $processors = new Processors();
        $databases = new Databases();
        $databases->add(new PdoDatabase(pdo: new PDO('sqlite::memory:'), name: 'memory'));
        
        $migration = new class($processors, $databases) extends DatabaseMigration
        {
            public function description(): string
            {
                return 'db migration';
            }
            
            public function install(): ActionsInterface
            {
                return new Actions(
                    new DatabaseAction(
                        processor: $this->processor,
                        database: $this->databases->get('memory'),
                        table: function(): Table {
                            $table = new Table(name: 'products');
                            $table->primary('id');
                            return $table;
                        },
                        name: 'Products',
                    ),
                    
                    // test only DatabaseAction get used for uninstall
                    new DatabaseDeleteAction(
                        processor: $this->processor,
                        database: $this->databases->get('memory'),
                        table: function(): Table {
                            $table = new Table(name: 'users');
                            $table->primary('id');
                            return $table;
                        },
                    ),
                );
            }
            
            public function uninstall(): ActionsInterface
            {
                return $this->createDatabaseDeleteActionsFromInstall();
            }
        };
        
        $actions = $migration->uninstall();
        $action = $actions->first();
        
        $this->assertSame(1, count($actions->all()));
        $this->assertSame('Products', $action?->name());
        $this->assertSame('products', $action?->table()->getName());
        $this->assertTrue($databases->get('memory') === $action?->database());
        $this->assertTrue($processors === $action?->processor());
    }    
}