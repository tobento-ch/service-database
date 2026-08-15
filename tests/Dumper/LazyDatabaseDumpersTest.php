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
use Tobento\Service\Container\Container;
use Tobento\Service\Database\Dumper\LazyDatabaseDumpers;
use Tobento\Service\Database\Dumper\DatabaseDumperInterface;
use Tobento\Service\Database\Dumper\DatabaseDumperFactoryInterface;
use Tobento\Service\Database\Dumper\DatabaseDumperNotFoundException;

class LazyDatabaseDumpersTest extends TestCase
{
    protected function createDumper(string $name = 'default'): DatabaseDumperInterface
    {
        return new class($name) implements DatabaseDumperInterface {
            public function __construct(private string $name) {}

            public function name(): string { return $this->name; }
            public function fileExtension(): string { return 'sql'; }
            public function dump(array $tables, $destination, ?float $timeoutSeconds = null): void {}
            public function restore($source, ?float $timeoutSeconds = null): void {}
        };
    }

    public function testImplementsInterface(): void
    {
        $dumpers = new LazyDatabaseDumpers(
            container: new Container(),
            dumpers: ['mysql' => $this->createDumper('mysql')]
        );

        $this->assertInstanceOf(
            \Tobento\Service\Database\Dumper\DatabaseDumpersInterface::class,
            $dumpers
        );
    }
    
    public function testHasMethod(): void
    {
        $dumpers = new LazyDatabaseDumpers(
            container: new Container(),
            dumpers: ['mysql' => $this->createDumper('mysql')]
        );

        $this->assertTrue($dumpers->has('mysql'));
        $this->assertFalse($dumpers->has('postgres'));
    }

    public function testGetMethodReturnsInstance(): void
    {
        $instance = $this->createDumper('mysql');

        $dumpers = new LazyDatabaseDumpers(
            container: new Container(),
            dumpers: ['mysql' => $instance]
        );

        $this->assertSame($instance, $dumpers->get('mysql'));
    }

    public function testGetMethodResolvesFactory(): void
    {
        $factory = new class implements DatabaseDumperFactoryInterface {
            public function createDumper(string $name, array $config = []): DatabaseDumperInterface
            {
                return new class($name) implements DatabaseDumperInterface {
                    public function __construct(private string $name) {}
                    public function name(): string { return $this->name; }
                    public function fileExtension(): string { return 'sql'; }
                    public function dump(array $tables, $destination, ?float $timeoutSeconds = null): void {}
                    public function restore($source, ?float $timeoutSeconds = null): void {}
                };
            }
        };

        $dumpers = new LazyDatabaseDumpers(
            container: new Container(),
            dumpers: ['mysql' => $factory]
        );

        $dumper = $dumpers->get('mysql');

        $this->assertSame('mysql', $dumper->name());
    }

    public function testGetMethodResolvesClassNameViaAutowire(): void
    {
        $container = new Container();

        // Register the class so autowire can resolve it
        $container->set(AutowireDumper::class, fn() => new AutowireDumper('mysql'));

        $dumpers = new LazyDatabaseDumpers(
            container: $container,
            dumpers: ['mysql' => AutowireDumper::class]
        );

        $dumper = $dumpers->get('mysql');

        $this->assertInstanceOf(DatabaseDumperInterface::class, $dumper);
        $this->assertSame('mysql', $dumper->name());
    }

    public function testGetMethodResolvesCallable(): void
    {
        $dumpers = new LazyDatabaseDumpers(
            container: new Container(),
            dumpers: [
                'mysql' => fn(string $name) => $this->createDumper($name)
            ]
        );

        $dumper = $dumpers->get('mysql');

        $this->assertSame('mysql', $dumper->name());
    }

    public function testGetMethodThrowsOnMissingDumper(): void
    {
        $dumpers = new LazyDatabaseDumpers(
            container: new Container(),
            dumpers: []
        );

        $this->expectException(DatabaseDumperNotFoundException::class);

        $dumpers->get('mysql');
    }

    public function testGetMethodThrowsOnInvalidEntryType(): void
    {
        $dumpers = new LazyDatabaseDumpers(
            container: new Container(),
            dumpers: ['mysql' => 123]
        );

        $this->expectException(DatabaseDumperNotFoundException::class);

        $dumpers->get('mysql');
    }
    
    public function testNamesMethod(): void
    {
        $dumpers = new LazyDatabaseDumpers(
            container: new Container(),
            dumpers: ['mysql' => $this->createDumper('mysql')]
        );

        $this->assertSame(['mysql'], $dumpers->names());
    }
}

class AutowireDumper implements DatabaseDumperInterface
{
    public function __construct(private string $name = 'mysql') {}

    public function name(): string { return $this->name; }
    public function fileExtension(): string { return 'sql'; }
    public function dump(array $tables, $destination, ?float $timeoutSeconds = null): void {}
    public function restore($source, ?float $timeoutSeconds = null): void {}
}