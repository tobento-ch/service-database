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

namespace Tobento\Service\Database;

use Throwable;

/**
 * Databases
 */
class Databases implements DatabasesInterface
{
    /**
     * @var array<string, callable|DatabaseInterface>
     */
    protected array $databases = [];
    
    /**
     * @var array<string, string> The default databases. ['pdo' => 'database']
     */
    protected array $defaults = [];    

    /**
     * Create a new Databases.
     *
     * @param DatabaseInterface $database
     */    
    public function __construct(
        DatabaseInterface ...$database,
    ) {
        $this->databases = $database;
    }
    
    /**
     * Add a database.
     *
     * @param DatabaseInterface $database
     * @return static $this
     */    
    public function add(DatabaseInterface $database): static
    {        
        $this->databases[$database->name()] = $database;
        return $this;
    }
    
    /**
     * Register a database.
     *
     * @param string $name The database name.
     * @param callable $database
     * @return static $this
     */    
    public function register(string $name, callable $database): static
    {
        $this->databases[$name] = $database;
        return $this;
    }
    
    /**
     * Returns the database by name.
     *
     * @param string $name The database name
     * @return DatabaseInterface
     * @throws DatabaseException
     */    
    public function get(string $name): DatabaseInterface
    {
        if (!$this->has($name))
        {
            throw new DatabaseException($name, 'Database ['.$name.'] not found!');
        }
        
        if (! $this->databases[$name] instanceof DatabaseInterface)
        {
            try {
                $this->databases[$name] = $this->createDatabase($name, $this->databases[$name]);
            } catch(Throwable $e) {
                throw new DatabaseException($name, $e->getMessage());
            }
        }
        
        return $this->databases[$name];
    }
    
    /**
     * Returns true if the database exists, otherwise false.
     *
     * @param string $name The database name.
     * @return bool
     */    
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->databases);
    }

    /**
     * Adds a default name for the specified database.
     *
     * @param string $name The default name.
     * @param string $database The database name.
     * @return static $this
     */    
    public function addDefault(string $name, string $database): static
    {
        $this->defaults[$name] = $database;
        return $this;
    }

    /**
     * Get the default databases.
     *
     * @return array<string, string> ['name' => 'database']
     */    
    public function getDefaults(): array
    {
        return $this->defaults;
    }
    
    /**
     * Get the database for the specified default name.
     *
     * @param string $name The type such as pdo.
     * @return DatabaseInterface
     * @throws DatabaseException
     */    
    public function default(string $name): DatabaseInterface
    {
        if (!$this->hasDefault($name))
        {
            throw new DatabaseException('', 'Default database ['.$name.'] not found!');
        }
        
        return $this->get($this->defaults[$name]);
    }
 
    /**
     * Returns true if the default database exists, otherwise false.
     *
     * @param string $name The default name.
     * @return bool
     */    
    public function hasDefault(string $name): bool
    {
        return array_key_exists($name, $this->defaults);
    }
    
    /**
     * Create a new Database.
     *
     * @param string $name
     * @param callable $factory
     * @return DatabaseInterface
     */    
    protected function createDatabase(string $name, callable $factory): DatabaseInterface
    { 
        return call_user_func_array($factory, [$name]);
    }    
}