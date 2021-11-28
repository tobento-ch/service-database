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

namespace Tobento\Service\Database;

use Exception;
use Throwable;

/**
 * DatabaseException
 */
class DatabaseException extends Exception
{
    /**
     * Create a new DatabaseException
     *
     * @param string $name The database name
     * @param string $message The message
     * @param int $code
     * @param null|Throwable $previous
     */
    public function __construct(
        protected string $name,
        string $message = '',
        int $code = 0,
        null|Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
    
    /**
     * Get the name of the database.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
}