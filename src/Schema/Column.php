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
 * Column
 */
abstract class Column implements ColumnInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $parameters = [];
    
    /**
     * Create a new Column.
     *
     * @param string $name The name of the column.
     */    
    public function __construct(
        protected string $name,
    ) {}

    /**
     * Returns the column type.
     *
     * @return string
     */    
    abstract public function getType(): string;

    /**
     * Returns a new instance with the specified name.
     *
     * @param string $name
     * @return static
     */    
    public function withName(string $name): static
    {
        $new = clone $this;
        $new->name = $name;        
        return $new;
    }
    
    /**
     * Returns the column name.
     *
     * @return string
     */    
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Add a parameter.
     *
     * @param string $name The name
     * @param mixed $value The value
     * @return static $this
     */
    public function parameter(string $name, mixed $value): static
    {        
        $this->parameters[$name] = $value;
        return $this;
    }
    
    /**
     * Returns the parameter value.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */    
    public function getParameter(string $name, mixed $default = null): mixed
    {
        return $this->parameters[$name] ?? $default;
    }    
}