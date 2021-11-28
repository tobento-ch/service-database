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
 * RenameColumn
 */
class RenameColumn extends Column
{    
    /**
     * Create a new RenameColumn.
     *
     * @param string $name The name of the column.
     * @param string $newName The new name of the column.
     */    
    public function __construct(
        protected string $name,
        protected string $newName,
    ) {}
 
    /**
     * Returns the column type.
     *
     * @return string
     */    
    public function getType(): string
    {
        return 'rename';
    }
    
    /**
     * Returns the new column name.
     *
     * @return string
     */    
    public function getNewName(): string
    {
        return $this->newName;
    }    
}