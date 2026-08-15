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

namespace Tobento\Service\Database\Dumper;

use Psr\Container\ContainerInterface;
use Tobento\Service\Autowire\Autowire;

class LazyDatabaseDumpers implements DatabaseDumpersInterface
{
    protected Autowire $autowire;

    public function __construct(
        ContainerInterface $container,
        protected array $dumpers = [],
    ) {
        $this->autowire = new Autowire($container);
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->dumpers);
    }

    public function get(string $name): DatabaseDumperInterface
    {
        if (!isset($this->dumpers[$name])) {
            throw new DatabaseDumperNotFoundException($name);
        }

        $entry = $this->dumpers[$name];

        // Already resolved instance
        if ($entry instanceof DatabaseDumperInterface) {
            return $entry;
        }

        // Factory instance
        if ($entry instanceof DatabaseDumperFactoryInterface) {
            return $this->dumpers[$name] = $entry->createDumper(name: $name);
        }

        // Class name → autowire
        if (is_string($entry)) {
            $this->dumpers[$name] = $this->autowire->resolve($entry);
            return $this->get($name);
        }

        // Callable factory
        if (is_callable($entry)) {
            $this->dumpers[$name] = $this->autowire->call($entry, ['name' => $name]);
            return $this->get($name);
        }

        throw new DatabaseDumperNotFoundException(
            sprintf('Unable to create database dumper "%s": invalid type', $name)
        );
    }

    public function names(): array
    {
        return array_keys($this->dumpers);
    }    
}