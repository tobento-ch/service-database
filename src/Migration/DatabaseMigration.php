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

use Tobento\Service\Migration\MigrationInterface;
use Tobento\Service\Migration\ActionsInterface;
use Tobento\Service\Migration\ActionInterface;
use Tobento\Service\Migration\Actions;
use Tobento\Service\Database\Processor\ProcessorInterface;
use Tobento\Service\Database\DatabasesInterface;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\Schema\Table;
use Closure;

/**
 * DatabaseMigration
 */
abstract class DatabaseMigration implements MigrationInterface
{
    /**
     * @var array
     */
    protected array $registeredTables = [];
    
    /**
     * Create a new DatabaseMigration.
     *
     * @param ProcessorInterface $processor
     * @param DatabasesInterface $databases
     */
    public function __construct(
        protected ProcessorInterface $processor,
        protected DatabasesInterface $databases,
    ) {
        $this->registerTables();
    }
    
    /**
     * Return a description of the migration.
     *
     * @return string
     */
    abstract public function description(): string;
    
    /**
     * Register tables used by the install and uninstall methods
     * to create the actions from.
     *
     * @return void
     */
    protected function registerTables(): void
    {
        //
    }
    
    /**
     * Return the actions to be processed on install.
     *
     * @return ActionsInterface
     */
    public function install(): ActionsInterface
    {
        $actions = [];
        
        foreach($this->registeredTables as [$table, $database, $name, $description]) {
            $actions[] = new DatabaseAction(
                processor: $this->processor,
                database: $database,
                table: $table,
                name: $name,
                description: $description,
            );
        }
        
        return new Actions(...$actions);
    }

    /**
     * Return the actions to be processed on uninstall.
     *
     * @return ActionsInterface
     */
    public function uninstall(): ActionsInterface
    {
        $actions = [];
        
        foreach($this->registeredTables as [$table, $database, $name, $description]) {
            $actions[] = new DatabaseDeleteAction(
                processor: $this->processor,
                database: $database,
                table: $table,
                name: $name,
                description: $description,
            );
        }
        
        return new Actions(...$actions);
    }
    
    /**
     * Register a table with its database.
     *
     * @param Table|Closure $table
     * @param DatabaseInterface $database
     * @param null|string $name A unique action name.
     * @param string $description A description of the action.     
     * @return void
     */
    protected function registerTable(
        Table|Closure $table,
        DatabaseInterface $database,
        null|string $name = null,
        string $description = '',
    ): void {
        
        if ($table instanceof Closure) {
            $table = $table();
        }
        
        $this->registeredTables[] = [$table, $database, $name, $description];
    }
    
    /**
     * Returns the actions created from the install method actions.
     *
     * @return ActionsInterface
     */
    protected function createDatabaseDeleteActionsFromInstall(): ActionsInterface
    {
        $actions = $this->install()->filter(
            fn(ActionInterface $a): bool => $a instanceof DatabaseAction
        );
        
        $deleteActions = [];
        
        foreach($actions as $action) {
            $deleteActions[] = new DatabaseDeleteAction(
                processor: $action->processor(),
                database: $action->database(),
                table: $action->table(),
                name: $action->name(),
                description: $action->description(),
            );
        }
        
        return new Actions(...$deleteActions);
    }
}