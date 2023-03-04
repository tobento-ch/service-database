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
use Tobento\Service\Database\Migration\DatabaseMigrationSeeder;
use Tobento\Service\Database\Databases;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\Processor\Processors;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Migration\ActionsInterface;
use Tobento\Service\Migration\Actions;
use Tobento\Service\Seeder\Seed;
use Tobento\Service\Seeder\Resources;
use Tobento\Service\Seeder\UserSeeder;
use Tobento\Service\Iterable\ItemFactoryIterator;
use PDO;

/**
 * DatabaseMigrationSeederTest
 */
class DatabaseMigrationSeederTest extends TestCase
{
    public function testInstallMethodReturnsEmptyActionIfNoneSpecified()
    {
        $processors = new Processors();
        $databases = new Databases();
        $seed = new Seed(new Resources());
        
        $migration = new class($processors, $databases, $seed) extends DatabaseMigrationSeeder
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
        $seed = new Seed(new Resources());
        $seed->addSeeder('user', new UserSeeder($seed));
        
        $migration = new class($processors, $databases, $seed) extends DatabaseMigrationSeeder
        {
            public function description(): string
            {
                return 'db migration';
            }
            
            protected function registerTables(): void
            {
                // test if available
                $fullname = $this->seed->fullname();
                
                $table = new Table(name: 'products');
                $table->primary('id');
                $table->string('name');
                $table->string('email');
                
                $table->items(new ItemFactoryIterator(
                    factory: function(): array {
                        return [
                            'name' => $this->seed->fullname(),
                            'email' => $this->seed->email(),
                        ];
                    },
                    create: 10
                ))
                ->chunk(length: 1000)
                ->useTransaction(false)
                ->forceInsert(true);
                
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

    public function testUninstallMethodWithRegisterTablesRetunsEmptyActions()
    {
        $processors = new Processors();
        $databases = new Databases();
        $databases->add(new PdoDatabase(pdo: new PDO('sqlite::memory:'), name: 'memory'));
        $seed = new Seed(new Resources());
        
        $migration = new class($processors, $databases, $seed) extends DatabaseMigrationSeeder
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
        
        $this->assertTrue($actions->empty());
    }
}