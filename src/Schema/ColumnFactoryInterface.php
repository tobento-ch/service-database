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
 * ColumnFactoryInterface
 */
interface ColumnFactoryInterface
{
    /**
     * Create a new Column.
     *
     * @param string $type The column type such as 'bigInt'.
     * @param string $name The column name.
     * @return ColumnInterface
     * @throws CreateColumnException
     */
    public function createColumn(string $type, string $name): ColumnInterface;
    
    /**
     * Create a new Column from array.
     *
     * @param array $column
     * @return ColumnInterface
     * @throws CreateColumnException
     */
    public function createColumnFromArray(array $column): ColumnInterface;
}