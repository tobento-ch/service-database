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

interface DatabasesInterface
{    
    /**
     * Register a database.
     *
     * @param string $name The database name.
     * @param callable $database
     * @return static $this
     */    
    public function register(string $name, callable $database): static;
    
    /**
     * Returns the database by name.
     *
     * @param string $name The database name
     * @return DatabaseInterface
     * @throws DatabaseException
     */    
    public function get(string $name): DatabaseInterface;
    
    /**
     * Returns true if the database exists, otherwise false.
     *
     * @param string $name The database name.
     * @return bool
     */    
    public function has(string $name): bool;
    
    /**
     * Returns all database names.
     *
     * @return array
     */
    public function names(): array;

    /**
     * Adds a default name for the specified database.
     *
     * @param string $name The default name.
     * @param string $database The database name.
     * @return static $this
     */    
    public function addDefault(string $name, string $database): static;

    /**
     * Get the default databases.
     *
     * @return array<string, string> ['name' => 'database']
     */    
    public function getDefaults(): array;
    
    /**
     * Get the database for the specified default name.
     *
     * @param string $name The type such as pdo.
     * @return DatabaseInterface
     * @throws DatabaseException
     */    
    public function default(string $name): DatabaseInterface;
 
    /**
     * Returns true if the default database exists, otherwise false.
     *
     * @param string $name The default name.
     * @return bool
     */    
    public function hasDefault(string $name): bool;
}