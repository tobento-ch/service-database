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

interface DatabaseInterface
{    
    /**
     * Returns the database name.
     *
     * @return string
     */    
    public function name(): string;
    
    /**
     * Returns the connection.
     *
     * @return mixed
     */    
    public function connection(): mixed;
    
    /**
     * Returns the value for the specified parameter name.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */    
    public function parameter(string $name, mixed $default = null): mixed;
}