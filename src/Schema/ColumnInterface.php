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

namespace Tobento\Service\Database\Schema;

/**
 * ColumnInterface
 */
interface ColumnInterface
{
    /**
     * Returns the column type.
     *
     * @return string
     */    
    public function getType(): string;
    
    /**
     * Returns the column name.
     *
     * @return string
     */    
    public function getName(): string;
    
    /**
     * Returns a new instance with the specified name.
     *
     * @param string $name
     * @return static
     */    
    public function withName(string $name): static;
    
    /**
     * Add a parameter.
     *
     * @param string $name The name
     * @param mixed $value The value
     * @return static $this
     */
    public function parameter(string $name, mixed $value): static;
    
    /**
     * Returns the parameter value.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */    
    public function getParameter(string $name, mixed $default = null): mixed;
}