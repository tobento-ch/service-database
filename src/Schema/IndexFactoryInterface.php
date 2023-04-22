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
 * IndexFactoryInterface
 */
interface IndexFactoryInterface
{
    /**
     * Create a new Index.
     *
     * @param string $name The index name.
     * @return IndexInterface
     */
    public function createIndex(string $name): IndexInterface;
    
    /**
     * Create a new Index from array.
     *
     * @param array $index
     * @return IndexInterface
     * @throws CreateIndexException
     */
    public function createIndexFromArray(array $index): IndexInterface;
}