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
 * TableFactory
 */
class TableFactory implements TableFactoryInterface
{
    /**
     * Create a new Table.
     *
     * @param string $name The table name.
     * @return Table
     */
    public function createTable(string $name): Table
    {
        return new Table(name: $name);
    }
}