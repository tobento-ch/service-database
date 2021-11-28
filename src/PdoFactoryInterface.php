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

use PDO;

interface PdoFactoryInterface
{
    /**
     * Create a new PDO instance based on the configuration.
     *
     * @param string $name Any database name.
     * @param array $config Configuration data.
     * @return PDO
     * @throws DatabaseException
     */    
    public function createPdo(string $name, array $config = []): PDO;
}