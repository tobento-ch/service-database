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

namespace Tobento\Service\Database\Dumper;

interface DatabaseDumperFactoryInterface
{
    /**
     * Create a new Database Dumper based on the configuration.
     *
     * @param string $name Any dumper name.
     * @param array $config Configuration data.
     * @return DatabaseDumperInterface
     * @throws DumperException
     */    
    public function createDumper(string $name, array $config = []): DatabaseDumperInterface;
}