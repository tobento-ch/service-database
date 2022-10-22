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
 * IndexFactory
 */
class IndexFactory implements IndexFactoryInterface
{
    /**
     * Create a new Index.
     *
     * @param string $name The index name.
     * @return IndexInterface
     */
    public function createIndex(string $name): IndexInterface
    {
        return new Index(name: $name);
    }
    
    /**
     * Create a new Index from array.
     *
     * @param array $index
     * @return IndexInterface
     * @throws CreateIndexException
     */
    public function createIndexFromArray(array $index): IndexInterface
    {
        if (!isset($index['name']) || !is_string($index['name'])) {
            throw new CreateIndexException($index, 'Missing or invalid index name');
        }
        
        $i = $this->createIndex($index['name']);
        
        // Column
        if (
            isset($index['column'])
            && (is_array($index['column']) || is_string($index['column']))
            && $i instanceof Index
        ) {
            if (is_string($index['column'])) {
                $index['column'] = [$index['column']];
            }
            
            $i->column(...$index['column']);   
        }
        
        // Unique
        if (
            array_key_exists('unique', $index)
            && is_bool($index['unique'])
            && $i instanceof Index
        ) {
            $i->unique($index['unique']);
        }
        
        // Primary
        if (
            array_key_exists('primary', $index)
            && is_bool($index['primary'])
            && $i instanceof Index
        ) {
            $i->primary($index['primary']);
        }
        
        // Rename
        if (
            isset($index['rename'])
            && is_string($index['rename'])
            && $i instanceof Index
        ) {
            $i->rename($index['rename']);
        }
        
        // Drop
        if (
            array_key_exists('drop', $index)
            && is_bool($index['drop'])
            && $i instanceof Index
        ) {
            $i->drop($index['drop']);
        }
        
        return $i;
    }
}