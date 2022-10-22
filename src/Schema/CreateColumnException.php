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
 * CreateColumnException
 */
class CreateColumnException extends Exception
{
    /**
     * Create a new CreateColumnException
     *
     * @param array $column
     * @param string $message The message
     * @param int $code
     * @param null|Throwable $previous
     */
    public function __construct(
        protected array $column,
        string $message = '',
        int $code = 0,
        null|Throwable $previous = null
    ) {
        if (empty($message)) {
            $message = 'Creating column of name ['.$this->name().'] and type ['.$this->type().'] failed!';
        }
        
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the column.
     *
     * @return array
     */
    public function column(): array
    {
        return $this->column;
    }
    
    /**
     * Returns the column type.
     *
     * @return string
     */
    public function type(): string
    {
        if (
            isset($this->column['type'])
            && is_string($this->column['type'])
        ) {
            return $this->column['type'];
        }
        
        return '';
    }
    
    /**
     * Returns the column name.
     *
     * @return string
     */
    public function name(): string
    {
        if (
            isset($this->column['name'])
            && is_string($this->column['name'])
        ) {
            return $this->column['name'];
        }
        
        return '';
    }
}