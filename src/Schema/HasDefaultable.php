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
 * HasDefaultable
 */
trait HasDefaultable
{
    /**
     * @var mixed
     */
    protected mixed $default = null;
    
    /**
     * Set a default value for the column.
     *
     * @param mixed $value
     * @return static $this
     */    
    public function default(mixed $value): static
    {
        $this->default = $value;
        return $this;
    }
    
    /**
     * Returns the default value if any.
     *
     * @return mixed
     */    
    public function getDefault(): mixed
    {
        return $this->default;
    }
}