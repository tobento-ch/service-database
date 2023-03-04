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

namespace Tobento\Service\Database\Migration;

use Tobento\Service\Migration\ActionsInterface;
use Tobento\Service\Migration\Actions;
use Tobento\Service\Database\Processor\ProcessorInterface;
use Tobento\Service\Database\DatabasesInterface;
use Tobento\Service\Seeder\SeedInterface;

/**
 * DatabaseMigrationSeeder
 */
abstract class DatabaseMigrationSeeder extends DatabaseMigration
{
    /**
     * Create a new DatabaseMigration.
     *
     * @param ProcessorInterface $processor
     * @param DatabasesInterface $databases
     * @param SeedInterface $seed
     */
    public function __construct(
        protected ProcessorInterface $processor,
        protected DatabasesInterface $databases,
        protected SeedInterface $seed,
    ) {
        $this->registerTables();
    }
    
    /**
     * Return the actions to be processed on uninstall.
     *
     * @return ActionsInterface
     */
    public function uninstall(): ActionsInterface
    {
        // return empty actions, otherwise table gets deleted by parent method.
        return new Actions();
    }
}