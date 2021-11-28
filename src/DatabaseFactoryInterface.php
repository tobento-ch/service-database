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

interface DatabaseFactoryInterface
{
    /**
     * Create a new Database based on the configuration.
     *
     * @param string $name Any database name.
     * @param array $config Configuration data.
     * @return DatabaseInterface
     * @throws DatabaseException
     */    
    public function createDatabase(string $name, array $config = []): DatabaseInterface;
}