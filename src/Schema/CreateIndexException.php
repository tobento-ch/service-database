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

use Exception;
use Throwable;

/**
 * CreateIndexException
 */
class CreateIndexException extends Exception
{
    /**
     * Create a new CreateIndexException
     *
     * @param array $index
     * @param string $message The message
     * @param int $code
     * @param null|Throwable $previous
     */
    public function __construct(
        protected array $index,
        string $message = '',
        int $code = 0,
        null|Throwable $previous = null
    ) {
        if (empty($message)) {
            $message = 'Creating index with name ['.$this->name().'] failed!';
        }
        
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the index.
     *
     * @return array
     */
    public function index(): array
    {
        return $this->index;
    }
    
    /**
     * Returns the index name.
     *
     * @return string
     */
    public function name(): string
    {
        if (
            isset($this->index['name'])
            && is_string($this->index['name'])
        ) {
            return $this->index['name'];
        }
        
        return '';
    }
}