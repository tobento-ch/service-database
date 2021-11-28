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
use Tobento\Service\Database\Processor\GrammarInterface;
use Tobento\Service\Database\Processor\GrammarException;
use Tobento\Service\Database\Processor\PdoMySqlGrammar;
use Tobento\Service\Database\Processor\Statements;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Database\Schema\PrimaryColumn;
use Tobento\Service\Database\Schema\BigPrimaryColumn;
use Tobento\Service\Database\Schema\BoolColumn;
use Tobento\Service\Database\Schema\IntColumn;
use Tobento\Service\Database\Schema\TinyIntColumn;
use Tobento\Service\Database\Schema\BigIntColumn;
use Tobento\Service\Database\Schema\CharColumn;
use Tobento\Service\Database\Schema\StringColumn;
use Tobento\Service\Database\Schema\TextColumn;
use Tobento\Service\Database\Schema\DoubleColumn;
use Tobento\Service\Database\Schema\FloatColumn;
use Tobento\Service\Database\Schema\DecimalColumn;
use Tobento\Service\Database\Schema\DatetimeColumn;
use Tobento\Service\Database\Schema\DateColumn;
use Tobento\Service\Database\Schema\TimeColumn;
use Tobento\Service\Database\Schema\TimestampColumn;
use Tobento\Service\Database\Schema\JsonColumn;
use Tobento\Service\Database\Schema\RenameColumn;
use Tobento\Service\Database\Schema\DropColumn;
use Tobento\Service\Database\Schema\Items;

/**
 * PdoMySqlGrammarTest
 */
class PdoMySqlGrammarTest extends TestCase
{    
    public function testImplementsGrammarInterface()
    {
        $grammar = new PdoMySqlGrammar();
        
        $this->assertInstanceOf(GrammarInterface::class, $grammar);
    }
    
    public function testReturnsStatements()
    {
        $table = new Table(name: 'products');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertInstanceOf(Statements::class, $statements);
    }
    
    public function testCreateTableWithPrimaryColumn()
    {
        $table = new Table(name: 'products');
        $table->primary('id');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'CREATE TABLE IF NOT EXISTS `products` (`id` int(11) UNSIGNED AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertFalse($statements->getStatements()[0]->isTransactionable());
        
        $this->assertSame(1, count($statements->getTable()->getColumns()));
        
        $this->assertInstanceOf(
            PrimaryColumn::class,
            $statements->getTable()->getColumns()['id']
        );        
    }
    
    public function testCreateTableWithBigPrimaryColumn()
    {
        $table = new Table(name: 'products');
        $table->bigPrimary('id');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'CREATE TABLE IF NOT EXISTS `products` (`id` bigint(20) UNSIGNED AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertFalse($statements->getStatements()[0]->isTransactionable());
        
        $this->assertSame(1, count($statements->getTable()->getColumns()));
        
        $this->assertInstanceOf(
            BigPrimaryColumn::class,
            $statements->getTable()->getColumns()['id']
        );
    }
    
    public function testCreateTableWithWithoutPrimaryColumn()
    {
        $table = new Table(name: 'products');
        $table->int('id');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'CREATE TABLE IF NOT EXISTS `products` (`id` int(11) UNSIGNED NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertFalse($statements->getStatements()[0]->isTransactionable());
        
        $this->assertSame(1, count($statements->getTable()->getColumns()));
    }    
    
    public function testCreateTableWithMultipleColumns()
    {
        $table = new Table(name: 'products');
        $table->bigPrimary('id');
        $table->string('name')->length(21);
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'CREATE TABLE IF NOT EXISTS `products` (`id` bigint(20) UNSIGNED AUTO_INCREMENT,`name` varchar(21) NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertFalse($statements->getStatements()[0]->isTransactionable());
        
        $this->assertSame(2, count($statements->getTable()->getColumns()));
    }
    
    public function testCreateTableWithNoColumns()
    {
        $table = new Table(name: 'products');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(0, count($statements->getStatements()));
        
        $this->assertSame(0, count($statements->getTable()->getColumns()));
    }
    
    public function testCreateTableWithTableParameters()
    {
        $table = new Table(name: 'products');
        $table->bigPrimary('id');
        
        $table->parameter('engine', 'MyISAM');
        $table->parameter('charset', 'utf8');
        $table->parameter('collation', 'utf8_unicode_ci');
        
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'CREATE TABLE IF NOT EXISTS `products` (`id` bigint(20) UNSIGNED AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertFalse($statements->getStatements()[0]->isTransactionable());        
    }
    
    public function testChangeColumnWithLengthableColumn()
    {
        $savedTable = new Table(name: 'products');
        $savedTable->int('id', 12); 
        
        $table = new Table(name: 'products');
        $table->int('id', 6);
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'ALTER TABLE `products` CHANGE COLUMN `id` `id` int(6) UNSIGNED NULL',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertTrue($statements->getStatements()[0]->isTransactionable());
        
        $this->assertSame(1, count($statements->getTable()->getColumns()));
    }
    
    public function testChangeColumnWithNullableColumn()
    {
        $savedTable = new Table(name: 'products');
        $savedTable->int('id', 12); 
        
        $table = new Table(name: 'products');
        $table->int('id', 6)->nullable(false);
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'ALTER TABLE `products` CHANGE COLUMN `id` `id` int(6) UNSIGNED NOT NULL',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertTrue($statements->getStatements()[0]->isTransactionable()); 
        
        $this->assertSame(1, count($statements->getTable()->getColumns()));
    }
    
    public function testChangeColumnWithDefaultableColumn()
    {
        $savedTable = new Table(name: 'products');
        $savedTable->int('id', 12); 
        
        $table = new Table(name: 'products');
        $table->int('id', 6)->default(10);
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'ALTER TABLE `products` CHANGE COLUMN `id` `id` int(6) UNSIGNED NULL DEFAULT 10',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertTrue($statements->getStatements()[0]->isTransactionable());
        
        $this->assertSame(1, count($statements->getTable()->getColumns()));
    }
    
    public function testChangeColumnWithUnsignableColumn()
    {
        $savedTable = new Table(name: 'products');
        $savedTable->int('id', 12); 
        
        $table = new Table(name: 'products');
        $table->int('id', 6)->unsigned(false);
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'ALTER TABLE `products` CHANGE COLUMN `id` `id` int(6) NULL',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertTrue($statements->getStatements()[0]->isTransactionable());
        
        $this->assertSame(1, count($statements->getTable()->getColumns()));
    }
    
    public function testChangeColumnWithMultipleColumns()
    {
        $savedTable = new Table(name: 'products');
        $savedTable->int('id', 12);
        $savedTable->string('name')->length(21);
        
        $table = new Table(name: 'products');
        $table->int('id', 6);
        $table->string('name')->length(30);
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(2, count($statements->getStatements()));
        
        $this->assertSame(
            'ALTER TABLE `products` CHANGE COLUMN `id` `id` int(6) UNSIGNED NULL',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame(
            'ALTER TABLE `products` CHANGE COLUMN `name` `name` varchar(30) NULL',
            $statements->getStatements()[1]->getStatement()
        );
        
        $this->assertSame(2, count($statements->getTable()->getColumns()));
    }
    
    public function testAddColumnWithLengthableColumn()
    {
        $savedTable = new Table(name: 'products');
        
        $table = new Table(name: 'products');
        $table->int('id', 6);
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'ALTER TABLE `products` ADD COLUMN `id` int(6) UNSIGNED NULL',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertTrue($statements->getStatements()[0]->isTransactionable());
        
        $this->assertSame(1, count($statements->getTable()->getColumns()));
    }
    
    public function testAddColumnWithNullableColumn()
    {
        $savedTable = new Table(name: 'products');
        
        $table = new Table(name: 'products');
        $table->int('id', 6)->nullable(false);
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'ALTER TABLE `products` ADD COLUMN `id` int(6) UNSIGNED NOT NULL',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertTrue($statements->getStatements()[0]->isTransactionable());
        
        $this->assertSame(1, count($statements->getTable()->getColumns()));
    }
    
    public function testAddColumnWithDefaultableColumn()
    {
        $savedTable = new Table(name: 'products');
        
        $table = new Table(name: 'products');
        $table->int('id', 6)->default(10);
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'ALTER TABLE `products` ADD COLUMN `id` int(6) UNSIGNED NULL DEFAULT 10',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertTrue($statements->getStatements()[0]->isTransactionable());
        
        $this->assertSame(1, count($statements->getTable()->getColumns()));
    }
    
    public function testAddColumnWithUnsignableColumn()
    {
        $savedTable = new Table(name: 'products');
        
        $table = new Table(name: 'products');
        $table->int('id', 6)->unsigned(false);
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'ALTER TABLE `products` ADD COLUMN `id` int(6) NULL',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertTrue($statements->getStatements()[0]->isTransactionable());
        
        $this->assertSame(1, count($statements->getTable()->getColumns()));
    }
    
    public function testAddColumnWithMultipleColumns()
    {
        $savedTable = new Table(name: 'products');
        
        $table = new Table(name: 'products');
        $table->int('id', 6);
        $table->string('name')->length(30);
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(2, count($statements->getStatements()));
        
        $this->assertSame(
            'ALTER TABLE `products` ADD COLUMN `id` int(6) UNSIGNED NULL',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame(
            'ALTER TABLE `products` ADD COLUMN `name` varchar(30) NULL AFTER `id`',
            $statements->getStatements()[1]->getStatement()
        );
        
        $this->assertSame(2, count($statements->getTable()->getColumns()));
    }
    
    public function testAddAndChangeColumns()
    {
        $savedTable = new Table(name: 'products');
        $savedTable->int('id', 6);
        
        $table = new Table(name: 'products');
        $table->int('id', 6);
        $table->string('name')->length(30);
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(2, count($statements->getStatements()));

        $this->assertSame(
            'ALTER TABLE `products` ADD COLUMN `name` varchar(30) NULL AFTER `id`',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame(
            'ALTER TABLE `products` CHANGE COLUMN `id` `id` int(6) UNSIGNED NULL',
            $statements->getStatements()[1]->getStatement()
        );
        
        $this->assertSame(2, count($statements->getTable()->getColumns()));
    }
    
    public function testStatementsTableName()
    {        
        $table = new Table(name: 'products');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);

        $this->assertSame(
            'products',
            $statements->getTable()->getName()
        );
    }     
    
    public function testStatementsTableReturningRightColumns()
    {        
        $table = new Table(name: 'products');
        $table->bool('bool');
        $table->int('int');
        $table->tinyInt('tinyInt');
        $table->bigInt('bigInt');
        $table->string('string');
        $table->float('float');
        $table->decimal('decimal');
        $table->datetime('datetime');
        $table->date('date');
        $table->time('time');
        $table->timestamp('timestamp');
        $table->json('json');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(12, count($statements->getTable()->getColumns()));
        
        $this->assertInstanceOf(
            BoolColumn::class,
            $statements->getTable()->getColumns()['bool']
        );
        
        $this->assertInstanceOf(
            IntColumn::class,
            $statements->getTable()->getColumns()['int']
        );
        
        $this->assertInstanceOf(
            TinyIntColumn::class,
            $statements->getTable()->getColumns()['tinyInt']
        );
        
        $this->assertInstanceOf(
            BigIntColumn::class,
            $statements->getTable()->getColumns()['bigInt']
        );
        
        $this->assertInstanceOf(
            FloatColumn::class,
            $statements->getTable()->getColumns()['float']
        );
        
        $this->assertInstanceOf(
            DecimalColumn::class,
            $statements->getTable()->getColumns()['decimal']
        );
        
        $this->assertInstanceOf(
            DatetimeColumn::class,
            $statements->getTable()->getColumns()['datetime']
        );
        
        $this->assertInstanceOf(
            DateColumn::class,
            $statements->getTable()->getColumns()['date']
        );
        
        $this->assertInstanceOf(
            TimeColumn::class,
            $statements->getTable()->getColumns()['time']
        ); 
        
        $this->assertInstanceOf(
            TimestampColumn::class,
            $statements->getTable()->getColumns()['timestamp']
        ); 
        
        $this->assertInstanceOf(
            JsonColumn::class,
            $statements->getTable()->getColumns()['json']
        );      
    }
    
    public function testRenameColumn()
    {
        $savedTable = new Table(name: 'products');
        $savedTable->int('id', 6);
        
        $table = new Table(name: 'products');
        $table->renameColumn('id', 'new_id');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));

        $this->assertSame(
            'ALTER TABLE `products` CHANGE COLUMN `id` `new_id` int(6) UNSIGNED NULL',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertTrue($statements->getStatements()[0]->isTransactionable());        
        
        $this->assertSame(1, count($statements->getTable()->getColumns()));
        
        $this->assertSame('new_id', $statements->getTable()->getColumns()['new_id']->getName());
    }
    
    public function testRenameColumnThrowsGrammarExceptionIfColumnDoesNotExist()
    {
        $this->expectException(GrammarException::class);
        
        $table = new Table(name: 'products');
        $table->renameColumn('id', 'new_id');
        
        $grammar = new PdoMySqlGrammar();
        
        $grammar->createStatements($table, null);
    }
    
    public function testRenameColumnHasBeenRenamedShouldNotThrowException()
    {
        $savedTable = new Table(name: 'products');
        $savedTable->int('new_id', 6);
        
        $table = new Table(name: 'products');
        $table->renameColumn('id', 'new_id');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(0, count($statements->getStatements()));

        $this->assertSame(1, count($statements->getTable()->getColumns()));
        
        $this->assertSame('new_id', $statements->getTable()->getColumns()['new_id']->getName());
    }
    
    public function testDropColumn()
    {
        $savedTable = new Table(name: 'products');
        $savedTable->int('id', 6);
        
        $table = new Table(name: 'products');
        $table->dropColumn('id');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));

        $this->assertSame(
            'ALTER TABLE `products` DROP COLUMN `id`',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertTrue($statements->getStatements()[0]->isTransactionable());
        
        $this->assertSame(0, count($statements->getTable()->getColumns()));
    }
    
    public function testDropColumnShouldSkipIfAlreadyDroppedOrNotExists()
    {
        $savedTable = new Table(name: 'products');
        
        $table = new Table(name: 'products');
        $table->dropColumn('id');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(0, count($statements->getStatements()));
        
        $this->assertSame(0, count($statements->getTable()->getColumns()));
    }
    
    public function testRenameTable()
    {
        $savedTable = new Table(name: 'products');
        
        $table = new Table(name: 'products');
        $table->renameTable('new_products');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));

        $this->assertSame(
            'ALTER TABLE `products` RENAME `new_products`',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertTrue($statements->getStatements()[0]->isTransactionable());        
        
        $this->assertSame('new_products', $statements->getTable()->getName());
        $this->assertSame(0, count($statements->getTable()->getColumns()));
    }
    
    public function testRenameTableWithoutSavedTable()
    {        
        $table = new Table(name: 'products');
        $table->renameTable('new_products');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame('products', $statements->getTable()->getName());
        $this->assertSame(0, count($statements->getStatements()));        
        $this->assertSame(0, count($statements->getTable()->getColumns()));
    }
    
    public function testRenameTableWithoutSavedTableWithColumnsShouldRenameTable()
    {
        $savedTable = new Table(name: 'products');
        
        $table = new Table(name: 'products');
        $table->int('name');
        $table->renameTable('new_products');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(2, count($statements->getStatements()));

        $this->assertSame(
            'CREATE TABLE IF NOT EXISTS `products` (`name` int(11) UNSIGNED NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame(
            'ALTER TABLE `products` RENAME `new_products`',
            $statements->getStatements()[1]->getStatement()
        );
        
        $this->assertSame('new_products', $statements->getTable()->getName());
        $this->assertSame(1, count($statements->getTable()->getColumns()));
    }
    
    public function testDropTable()
    {
        $savedTable = new Table(name: 'products');
        
        $table = new Table(name: 'products');
        $table->dropTable();
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));

        $this->assertSame(
            'DROP TABLE IF EXISTS `products`',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertFalse($statements->getStatements()[0]->isTransactionable());        
        
        $this->assertTrue($statements->getTable()->dropping());
        $this->assertSame(0, count($statements->getTable()->getColumns()));
    }
    
    public function testDropTableWithColumns()
    {
        $savedTable = new Table(name: 'products');
        
        $table = new Table(name: 'products');
        $table->int('name');
        $table->dropTable();
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(2, count($statements->getStatements()));

        $this->assertSame(
            'ALTER TABLE `products` ADD COLUMN `name` int(11) UNSIGNED NULL',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame(
            'DROP TABLE IF EXISTS `products`',
            $statements->getStatements()[1]->getStatement()
        ); 
        
        $this->assertTrue($statements->getTable()->dropping());
        $this->assertSame(1, count($statements->getTable()->getColumns()));
    }
    
    public function testDropTableWithoutSaveTable()
    {        
        $table = new Table(name: 'products');
        $table->dropTable();
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(1, count($statements->getStatements()));
        
        $this->assertSame(
            'DROP TABLE IF EXISTS `products`',
            $statements->getStatements()[0]->getStatement()
        ); 
        
        $this->assertTrue($statements->getTable()->dropping());
        $this->assertSame(0, count($statements->getTable()->getColumns()));
    }
    
    public function testTruncateTable()
    {
        $savedTable = new Table(name: 'products');
        
        $table = new Table(name: 'products');
        $table->truncate();
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));

        $this->assertSame(
            'TRUNCATE `products`',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[0]->getBindings());
        
        $this->assertTrue($statements->getStatements()[0]->isTransactionable());        

        $this->assertSame(0, count($statements->getTable()->getColumns()));
    }
    
    public function testTruncateTableWithoutSavedTableButWithCreateColumns()
    {        
        $table = new Table(name: 'products');
        $table->int('name');
        $table->truncate();
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(2, count($statements->getStatements()));

        $this->assertSame(
            'TRUNCATE `products`',
            $statements->getStatements()[1]->getStatement()
        );

        $this->assertSame(1, count($statements->getTable()->getColumns()));
    }
    
    public function testTruncateTableWithoutSavedTableAndCreateColumns()
    {        
        $table = new Table(name: 'products');
        $table->truncate();
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(0, count($statements->getStatements()));    

        $this->assertSame(0, count($statements->getTable()->getColumns()));
    }

    public function testIndexShouldBeSkippedIfTheColumnDoesNotExist()
    {        
        $table = new Table(name: 'products');
        $table->index('index_name')->column('name');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(0, count($statements->getStatements()));  

        $this->assertSame(0, count($statements->getTable()->getColumns()));
    }
    
    public function testIndexShouldBeSkippedIfTheColumnDoesIsBeingDropped()
    {        
        $table = new Table(name: 'products');
        $table->dropColumn('name');
        
        $table->index('index_name')->column('name');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(0, count($statements->getStatements()));  

        $this->assertSame(0, count($statements->getTable()->getColumns()));
    }    
    
    public function testSimpleIndex()
    {        
        $table = new Table(name: 'products');
        $table->int('foo');
        $table->index('index_name')->column('foo');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(2, count($statements->getStatements()));

        $this->assertSame(
            'ALTER TABLE `products` ADD KEY `index_name` (`foo`)',
            $statements->getStatements()[1]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[1]->getBindings());
        
        $this->assertTrue($statements->getStatements()[1]->isTransactionable());        

        $this->assertSame(1, count($statements->getTable()->getColumns()));
        
        $this->assertSame(1, count($statements->getTable()->getIndexes()));
        
        $index = $statements->getTable()->getIndexes()['index_name'];
        
        $this->assertSame('index_name', $index->getName());
        
        $this->assertSame(['foo'], $index->getColumns());
        
        $this->assertFalse($index->isUnique());
        
        $this->assertFalse($index->isPrimary());
        
        $this->assertSame(null, $index->getRename());
        
        $this->assertFalse($index->dropping());
    }
    
    public function testCompoundIndex()
    {        
        $table = new Table(name: 'products');
        $table->int('foo');
        $table->int('bar');
        $table->index('index_name')->column('foo', 'bar');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(2, count($statements->getStatements()));

        $this->assertSame(
            'ALTER TABLE `products` ADD KEY `index_name` (`foo`,`bar`)',
            $statements->getStatements()[1]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[1]->getBindings());
        
        $this->assertTrue($statements->getStatements()[1]->isTransactionable());        

        $this->assertSame(2, count($statements->getTable()->getColumns()));
        
        $this->assertSame(1, count($statements->getTable()->getIndexes()));
        
        $index = $statements->getTable()->getIndexes()['index_name'];
        
        $this->assertSame('index_name', $index->getName());
        
        $this->assertSame(['foo', 'bar'], $index->getColumns());
        
        $this->assertFalse($index->isUnique());
        
        $this->assertFalse($index->isPrimary());
        
        $this->assertSame(null, $index->getRename());
        
        $this->assertFalse($index->dropping());        
    }
    
    public function testSimpleUniqueIndex()
    {        
        $table = new Table(name: 'products');
        $table->int('foo');
        $table->index('index_name')->column('foo')->unique();
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(2, count($statements->getStatements()));

        $this->assertSame(
            'ALTER TABLE `products` ADD UNIQUE KEY `index_name` (`foo`)',
            $statements->getStatements()[1]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[1]->getBindings());
        
        $this->assertTrue($statements->getStatements()[1]->isTransactionable());        

        $this->assertSame(1, count($statements->getTable()->getColumns()));
        
        $this->assertSame(1, count($statements->getTable()->getIndexes()));
        
        $index = $statements->getTable()->getIndexes()['index_name'];
        
        $this->assertSame('index_name', $index->getName());
        
        $this->assertSame(['foo'], $index->getColumns());
        
        $this->assertTrue($index->isUnique());
        
        $this->assertFalse($index->isPrimary());
        
        $this->assertSame(null, $index->getRename());
        
        $this->assertFalse($index->dropping());        
    }
    
    public function testCompoundUniqueIndex()
    {        
        $table = new Table(name: 'products');
        $table->int('foo');
        $table->int('bar');
        $table->index('index_name')->column('foo', 'bar')->unique();
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(2, count($statements->getStatements()));

        $this->assertSame(
            'ALTER TABLE `products` ADD UNIQUE KEY `index_name` (`foo`,`bar`)',
            $statements->getStatements()[1]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[1]->getBindings());
        
        $this->assertTrue($statements->getStatements()[1]->isTransactionable());        

        $this->assertSame(2, count($statements->getTable()->getColumns()));
        
        $this->assertSame(1, count($statements->getTable()->getIndexes()));
        
        $index = $statements->getTable()->getIndexes()['index_name'];
        
        $this->assertSame('index_name', $index->getName());
        
        $this->assertSame(['foo', 'bar'], $index->getColumns());
        
        $this->assertTrue($index->isUnique());
        
        $this->assertFalse($index->isPrimary());
        
        $this->assertSame(null, $index->getRename());
        
        $this->assertFalse($index->dropping());        
    }
    
    public function testPrimaryIndex()
    {        
        $table = new Table(name: 'products');
        $table->int('foo');
        $table->index()->column('foo')->primary();
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(2, count($statements->getStatements()));

        $this->assertSame(
            'ALTER TABLE `products` ADD PRIMARY KEY (`foo`)',
            $statements->getStatements()[1]->getStatement()
        );
        
        $this->assertSame([], $statements->getStatements()[1]->getBindings());
        
        $this->assertTrue($statements->getStatements()[1]->isTransactionable());        

        $this->assertSame(1, count($statements->getTable()->getColumns()));
        
        $this->assertSame(1, count($statements->getTable()->getIndexes()));
        
        $index = $statements->getTable()->getIndexes()[''];
        
        $this->assertSame('', $index->getName());
        
        $this->assertSame(['foo'], $index->getColumns());
        
        $this->assertFalse($index->isUnique());
        
        $this->assertTrue($index->isPrimary());
        
        $this->assertSame(null, $index->getRename());
        
        $this->assertFalse($index->dropping());        
    }
    
    public function testRenameIndex()
    {        
        $savedTable = new Table(name: 'products');
        $savedTable->int('foo');
        $savedTable->index('foo')->column('foo');
            
        $table = new Table(name: 'products');
        $table->index('foo')->rename('new_foo');
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(2, count($statements->getStatements()));

        $this->assertSame(
            'ALTER TABLE `products` DROP INDEX `foo`',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame(
            'ALTER TABLE `products` ADD KEY `new_foo` (`foo`)',
            $statements->getStatements()[1]->getStatement()
        );  
        
        $this->assertSame(1, count($statements->getTable()->getIndexes()));
        
        $index = $statements->getTable()->getIndexes()['new_foo'];
        
        $this->assertSame('new_foo', $index->getName());
        
        $this->assertSame(['foo'], $index->getColumns());
        
        $this->assertFalse($index->isUnique());
        
        $this->assertFalse($index->isPrimary());
        
        $this->assertSame(null, $index->getRename());
        
        $this->assertFalse($index->dropping());        
    }
    
    public function testDropIndex()
    {        
        $savedTable = new Table(name: 'products');
        $savedTable->int('foo');
        $savedTable->index('foo')->column('foo');
            
        $table = new Table(name: 'products');
        $table->index('foo')->drop();
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));

        $this->assertSame(
            'ALTER TABLE `products` DROP INDEX `foo`',
            $statements->getStatements()[0]->getStatement()
        ); 
        
        $this->assertSame(0, count($statements->getTable()->getIndexes()));     
    }
    
    public function testItems()
    {
        $table = new Table(name: 'products');

        $table->items(new Items([
            ['name' => 'foo', 'active' => true],
            ['name' => 'bar', 'active' => true],
        ]))
        ->chunk(length: 100)
        ->useTransaction(true) // default is true
        ->forceInsert(false); // default is false
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(1, count($statements->getStatements()));

        $this->assertSame(
            'INSERT INTO `products` (`name`,`active`) VALUES (?, ?),(?, ?)',
            $statements->getStatements()[0]->getStatement()
        ); 
        
        $this->assertSame(
            ['foo', true, 'bar', true],
            $statements->getStatements()[0]->getBindings()
        );
        
        $this->assertTrue($statements->getStatements()[0]->isTransactionable());  
    }
    
    public function testItemsChunking()
    {
        $table = new Table(name: 'products');

        $table->items(new Items([
            ['name' => 'foo', 'active' => true],
            ['name' => 'bar', 'active' => true],
        ]))
        ->chunk(length: 1)
        ->useTransaction(true) // default is true
        ->forceInsert(false); // default is false
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(2, count($statements->getStatements()));

        $this->assertSame(
            'INSERT INTO `products` (`name`,`active`) VALUES (?, ?)',
            $statements->getStatements()[0]->getStatement()
        );
        
        $this->assertSame(
            ['foo', true],
            $statements->getStatements()[0]->getBindings()
        );
        
        $this->assertSame(
            'INSERT INTO `products` (`name`,`active`) VALUES (?, ?)',
            $statements->getStatements()[1]->getStatement()
        );
        
        $this->assertSame(
            ['bar', true],
            $statements->getStatements()[1]->getBindings()
        ); 
    }
    
    public function testItemsUsesTransaction()
    {
        $table = new Table(name: 'products');

        $table->items(new Items([
            ['name' => 'foo', 'active' => true],
            ['name' => 'bar', 'active' => true],
        ]))
        ->chunk(length: 100)
        ->useTransaction(false) // default is true
        ->forceInsert(false); // default is false
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, null);
        
        $this->assertSame(1, count($statements->getStatements()));

        $this->assertSame(
            'INSERT INTO `products` (`name`,`active`) VALUES (?, ?),(?, ?)',
            $statements->getStatements()[0]->getStatement()
        ); 
        
        $this->assertSame(
            ['foo', true, 'bar', true],
            $statements->getStatements()[0]->getBindings()
        );
        
        $this->assertFalse($statements->getStatements()[0]->isTransactionable());  
    }
    
    public function testItemsIfForceInsertIsFalseShouldNotInsertIfItemsExists()
    {
        $savedTable = new Table(name: 'products');
        $savedTable->itemsCount(1);
        
        $table = new Table(name: 'products');

        $table->items(new Items([
            ['name' => 'foo', 'active' => true],
            ['name' => 'bar', 'active' => true],
        ]))
        ->chunk(length: 100)
        ->useTransaction(true) // default is true
        ->forceInsert(false); // default is false
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(0, count($statements->getStatements()));
    }
    
    public function testItemsIfForceInsertIsTrueShouldInsertEvenIfItemsExists()
    {
        $savedTable = new Table(name: 'products');
        $savedTable->itemsCount(1);
        
        $table = new Table(name: 'products');

        $table->items(new Items([
            ['name' => 'foo', 'active' => true],
            ['name' => 'bar', 'active' => true],
        ]))
        ->chunk(length: 100)
        ->useTransaction(true) // default is true
        ->forceInsert(true); // default is false
        
        $grammar = new PdoMySqlGrammar();
        
        $statements = $grammar->createStatements($table, $savedTable);
        
        $this->assertSame(1, count($statements->getStatements()));
    }    
}