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
 * Index
 */
class Index implements IndexInterface
{
    /**
     * @var array<int, string>
     */
    protected array $columns = [];
    
    /**
     * @var bool
     */
    protected bool $unique = false;
    
    /**
     * @var bool
     */
    protected bool $primary = false;
    
    /**
     * @var null|string
     */
    protected null|string $rename = null;
    
    /**
     * @var bool
     */
    protected bool $drop = false;
    
    /**
     * Create a new Index.
     *
     * @param string $name The name of the index.
     */    
    public function __construct(
        protected string $name,
    ) {}

    /**
     * Returns the index type.
     *
     * @return string
     */    
    public function getType(): string
    {
        return 'index';
    }

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
        $new->rename = null;
        return $new;
    }
    
    /**
     * Returns the index name.
     *
     * @return string
     */    
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Set the column name(s).
     *
     * @param string $column
     * @return static $this
     */    
    public function column(string ...$column): static
    {
        $this->columns = $column;
        return $this;
    }
    
    /**
     * Returns the columns.
     *
     * @return array<int, string>
     */    
    public function getColumns(): array
    {
        return $this->columns;
    }
    
    /**
     * Set whether the index is unique.
     *
     * @param bool $unique
     * @return static $this
     */    
    public function unique(bool $unique = true): static
    {
        $this->unique = $unique;
        return $this;
    }
    
    /**
     * Returns whether the index is unique.
     *
     * @return bool
     */    
    public function isUnique(): bool
    {
        return $this->unique;
    }
    
    /**
     * Set whether the index is primary.
     *
     * @param bool $primary
     * @return static $this
     */    
    public function primary(bool $primary = true): static
    {
        $this->primary = $primary;
        return $this;
    }
    
    /**
     * Returns whether the index is primary.
     *
     * @return bool
     */    
    public function isPrimary(): bool
    {
        return $this->primary;
    }

    /**
     * Set whether to rename the index.
     *
     * @param string $rename
     * @return static $this
     */    
    public function rename(string $rename): static
    {
        $this->rename = $rename;
        return $this;
    }
    
    /**
     * Returns the rename of the index if one.
     *
     * @return null|string
     */    
    public function getRename(): null|string
    {
        return $this->rename;
    }
    
    /**
     * Set whether to drop the index.
     *
     * @param bool $drop
     * @return static $this
     */    
    public function drop(bool $drop = true): static
    {
        $this->drop = $drop;
        return $this;
    }
    
    /**
     * Returns whether to drop the index.
     *
     * @return bool
     */    
    public function dropping(): bool
    {
        return $this->drop;
    }
}