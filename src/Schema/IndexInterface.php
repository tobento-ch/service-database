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
 * IndexInterface
 */
interface IndexInterface
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
     * Returns the columns.
     *
     * @return array<int, string>
     */    
    public function getColumns(): array;
    
    /**
     * Returns whether the index is unique.
     *
     * @return bool
     */    
    public function isUnique(): bool;
    
    /**
     * Returns whether the index is primary.
     *
     * @return bool
     */    
    public function isPrimary(): bool;
    
    /**
     * Returns the rename of the index if one.
     *
     * @return null|string
     */    
    public function getRename(): null|string;
    
    /**
     * Returns whether to drop the index.
     *
     * @return bool
     */    
    public function dropping(): bool;
    
    /**
     * Set whether to drop the index.
     *
     * @param bool $drop
     * @return static $this
     */    
    public function drop(bool $drop = true): static;  
}