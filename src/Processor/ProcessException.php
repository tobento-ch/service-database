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

namespace Tobento\Service\Database\Processor;

use Exception;
use Throwable;

/**
 * ProcessException
 */
class ProcessException extends Exception
{
    /**
     * Create a new ProcessException.
     *
     * @param string $message The message
     * @param int $code 
     * @param null|Throwable $previous
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        null|Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}