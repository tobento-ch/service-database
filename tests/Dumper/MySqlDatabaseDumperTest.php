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

namespace Tobento\Service\Database\Test\Dumper;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Database\Dumper\MySqlDatabaseDumper;
use Tobento\Service\Database\Dumper\DatabaseDumperInterface;
use Tobento\Service\Database\Dumper\DumpException;
use Tobento\Service\Database\Dumper\RestoreException;

class MySqlDatabaseDumperTest extends TestCase
{
    protected function setUp(): void
    {
        if (! getenv('TEST_TOBENTO_DATABASE_MYSQL_DUMPER')) {
            $this->markTestSkipped('MySqlDatabaseDumper tests are disabled');
        }
    }

    protected function createDumper(): MySqlDatabaseDumper
    {
        $port = getenv('TEST_TOBENTO_DATABASE_MYSQL_PORT');
        $port = $port !== false && $port !== '' ? (int)$port : null;
        
        return new MySqlDatabaseDumper(
            name: 'mysql',
            database: getenv('TEST_TOBENTO_DATABASE_MYSQL_NAME'),
            host: getenv('TEST_TOBENTO_DATABASE_MYSQL_HOST'),
            user: getenv('TEST_TOBENTO_DATABASE_MYSQL_USER'),
            password: getenv('TEST_TOBENTO_DATABASE_MYSQL_PASSWORD'),
            port: $port,
            socket: getenv('TEST_TOBENTO_DATABASE_MYSQL_SOCKET') ?: null,
        );
    }

    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(
            DatabaseDumperInterface::class,
            $this->createDumper()
        );
    }
    
    public function testGetterMethods(): void
    {
        $dumper = $this->createDumper();
        $this->assertSame('mysql', $dumper->name());
        $this->assertSame('sql', $dumper->fileExtension());
    }

    public function testDumpMethodCreatesSqlStream(): void
    {
        $dumper = $this->createDumper();

        // Create table for dump
        $pdo = new \PDO(
            getenv('TEST_TOBENTO_DATABASE_MYSQL_DSN'),
            getenv('TEST_TOBENTO_DATABASE_MYSQL_USER'),
            getenv('TEST_TOBENTO_DATABASE_MYSQL_PASSWORD')
        );

        $pdo->exec("DROP TABLE IF EXISTS users");
        $pdo->exec("CREATE TABLE users (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255))");
        $pdo->exec("INSERT INTO users (name) VALUES ('Alice'), ('Bob')");

        $stream = fopen('php://temp', 'w+');

        $dumper->dump(['users'], $stream);

        rewind($stream);
        $output = stream_get_contents($stream);

        $this->assertStringContainsString('CREATE TABLE', $output);
        $this->assertStringContainsString('INSERT INTO', $output);
    }
    
    public function testDumpMethodThrowsExceptionOnInvalidTable(): void
    {
        $this->expectException(DumpException::class);

        $dumper = $this->createDumper();
        $stream = fopen('php://temp', 'w+');

        $dumper->dump(['non_existing_table'], $stream);
    }
    
    public function testDumpMethodThrowsExceptionOnInvalidDestination(): void
    {
        $dumper = $this->createDumper();

        $this->expectException(DumpException::class);

        $dumper->dump(['users'], 'not-a-stream');
    }

    public function testDumpMethodThrowsExceptionOnEmptyTableList(): void
    {
        $dumper = $this->createDumper();

        $stream = fopen('php://temp', 'w+');

        $this->expectException(DumpException::class);

        $dumper->dump([], $stream);
    }

    public function testRestoreMethodExecutesSql(): void
    {
        $dumper = $this->createDumper();

        // Ensure clean state
        $pdo = new \PDO(
            getenv('TEST_TOBENTO_DATABASE_MYSQL_DSN'),
            getenv('TEST_TOBENTO_DATABASE_MYSQL_USER'),
            getenv('TEST_TOBENTO_DATABASE_MYSQL_PASSWORD')
        );
        $pdo->exec("DROP TABLE IF EXISTS dumper_test");

        // SQL to restore
        $sql = <<<SQL
    CREATE TABLE dumper_test (id INT PRIMARY KEY);
    INSERT INTO dumper_test VALUES (1);
    SQL;

        $stream = fopen('php://temp', 'w+');
        fwrite($stream, $sql);
        rewind($stream);

        // Perform restore
        $dumper->restore($stream);

        // Verify
        $result = $pdo->query("SELECT COUNT(*) FROM dumper_test")->fetchColumn();
        $this->assertSame(1, $result);

        // Cleanup
        $pdo->exec("DROP TABLE dumper_test");
    }

    public function testRestoreMethodThrowsExceptionOnInvalidSql(): void
    {
        $this->expectException(RestoreException::class);

        $dumper = $this->createDumper();

        $stream = fopen('php://temp', 'w+');
        fwrite($stream, 'INVALID SQL');
        rewind($stream);

        $dumper->restore($stream);
    }
}