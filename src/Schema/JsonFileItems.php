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

use Tobento\Service\Filesystem\JsonFile;
use Closure;

/**
 * JsonFileItems
 */
class JsonFileItems implements ItemsInterface
{
    /**
     * @var array
     */
    protected array $data = [];
    
    /**
     * @var null|Closure
     */
    protected null|Closure $mapper = null;    
    
    /**
     * @var int
     */
    protected int $chunk = 10;
    
    /**
     * @var bool
     */
    protected bool $useTransaction = true;
    
    /**
     * @var bool
     */
    protected bool $forceInsert = false;    
    
    /**
     * @var int
     */
    private int $position = 0;

    /**
     * Create a new JsonFileItems.
     *
     * @param string $file
     */    
    public function __construct(
        string $file,
    ) {
        $this->data = (new JsonFile($file))->toArray();
    }
    
    /**
     * Map the item.
     *
     * @param Closure $mapper
     * @return static $this
     */    
    public function map(Closure $mapper): static
    {
        $this->mapper = $mapper;
        return $this;
    }
    
    /**
     * Set the chunk length.
     *
     * @param int $length
     * @return static $this
     */    
    public function chunk(int $length): static
    {
        $this->chunk = $length;
        return $this;
    }
    
    /**
     * Set if to use transaction.
     *
     * @param bool $use
     * @return static $this
     */    
    public function useTransaction(bool $use): static
    {
        $this->useTransaction = $use;
        return $this;
    }
    
    /**
     * Set if to use force insert.
     *
     * @param bool $forceInsert
     * @return static $this
     */    
    public function forceInsert(bool $forceInsert): static
    {
        $this->forceInsert = $forceInsert;
        return $this;
    }    
    
    /**
     * Returns the chunk length.
     *
     * @return int
     */    
    public function getChunkLength(): int
    {
        return $this->chunk;
    }
    
    /**
     * Returns whether to use transaction.
     *
     * @return bool
     */    
    public function withTransaction(): bool
    {
        return $this->useTransaction;
    }

    /**
     * Returns whether to force insert.
     *
     * @return bool
     */    
    public function forcingInsert(): bool
    {
        return $this->forceInsert;
    }
    
    public function rewind()
    {
        
        $this->position = 0;
    }

    public function current()
    {
        if (!is_null($this->mapper)) {
            return call_user_func_array($this->mapper, [$this->data[$this->position]]);
        }
        
        return $this->data[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->data[$this->position]);
    }
}