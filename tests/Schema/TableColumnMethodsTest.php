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

namespace Tobento\Service\Database\Test\Schema;

use PHPUnit\Framework\TestCase;
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

/**
 * TableColumnMethodsTest tests
 */
class TableColumnMethodsTest extends TestCase
{
    public function testPrimary()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            PrimaryColumn::class,
            $table->primary('name')
        );        
    }
    
    public function testBigPrimary()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            BigPrimaryColumn::class,
            $table->bigPrimary('name')
        );        
    }
    
    public function testBool()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            BoolColumn::class,
            $table->bool('name')
        );        
    }
    
    public function testInt()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            IntColumn::class,
            $table->int('name')
        );
        
        $this->assertInstanceOf(
            IntColumn::class,
            $table->int('name', 6)
        );        
    }
    
    public function testTinyInt()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            TinyIntColumn::class,
            $table->tinyInt('name')
        );
        
        $this->assertInstanceOf(
            TinyIntColumn::class,
            $table->tinyInt('name', 2)
        );        
    }
    
    public function testBigInt()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            BigIntColumn::class,
            $table->bigInt('name')
        );
        
        $this->assertInstanceOf(
            BigIntColumn::class,
            $table->bigInt('name', 18)
        );         
    }

    public function testChar()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            CharColumn::class,
            $table->char('name')
        );
    
        $this->assertInstanceOf(
            CharColumn::class,
            $table->char('name', 100)
        );
    }
    
    public function testString()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            StringColumn::class,
            $table->string('name')
        );
    
        $this->assertInstanceOf(
            StringColumn::class,
            $table->string('name', 100)
        );
    }
    
    public function testText()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            TextColumn::class,
            $table->text('name')
        );
    }
    
    public function testDouble()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            DoubleColumn::class,
            $table->double('name')
        );
    }
    
    public function testFloat()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            FloatColumn::class,
            $table->float('name')
        );
    }
    
    public function testDecimal()
    {
        $table = new Table('users');

        $this->assertInstanceOf(
            DecimalColumn::class,
            $table->decimal('name')
        );        
        
        $this->assertInstanceOf(
            DecimalColumn::class,
            $table->decimal('name', 10, 1)
        );
    }
    
    public function testDatetime()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            DatetimeColumn::class,
            $table->datetime('name')
        );
    }
    
    public function testDate()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            DateColumn::class,
            $table->date('name')
        );
    }
    
    public function testTime()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            TimeColumn::class,
            $table->time('name')
        );
    }
    
    public function testTimestamp()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            TimestampColumn::class,
            $table->timestamp('name')
        );
    }
    
    public function testJson()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            JsonColumn::class,
            $table->json('name')
        );
    }
    
    public function testRenameColumn()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            RenameColumn::class,
            $table->renameColumn('from', 'to')
        );
    }
    
    public function testDropColumn()
    {
        $table = new Table('users');
        
        $this->assertInstanceOf(
            DropColumn::class,
            $table->dropColumn('from', 'to')
        );
    }     
}