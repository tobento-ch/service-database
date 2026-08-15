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

interface DatabaseDumpersInterface
{
    /**
     * Returns true if a dumper exists for the given database name.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Returns the dumper for the given database name.
     *
     * @param string $name
     * @return DatabaseDumperInterface
     * @throws DatabaseDumperNotFoundException
     */
    public function get(string $name): DatabaseDumperInterface;

    /**
     * Returns all registered dumper names.
     *
     * @return array<array-key, string>
     */
    public function names(): array;
}