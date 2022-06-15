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
use PDOStatement;
use Throwable;

/**
 * PdoDatabase
 */
class PdoDatabase implements DatabaseInterface, PdoDatabaseInterface
{
    /**
     * @var int
     */
    protected int $transactionLevel = 0;
    
    /**
     * @var array Database drivers that support savepoints.
     */
    protected array $supportsSavepoints = ['pgsql', 'mysql'];    
    
    /**
     * Create a new PdoDatabase.
     *
     * @param PDO $pdo
     * @param string $name
     * @param array $parameters
     */    
    public function __construct(
        protected PDO $pdo,
        protected string $name = 'pdo',
        protected array $parameters = [],
    ) {}

    /**
     * Execute a statement.
     *
     * @param string $statement The statement.
     * @param array $bindings Any bindings for the statement.
     * @return PDOStatement
     */    
    public function execute(string $statement, array $bindings = []): PDOStatement
    {
        $statement = $this->pdo()->prepare($statement);
        
        $this->bindValues($statement, $this->prepareBindings($bindings));
        
        $statement->execute();
        
        return $statement;
    }
    
    /**
     * Begin a transaction.
     *
     * @return bool Returns true on success or false on failure.
     */
    public function begin(): bool
    {
        if($this->transactionLevel === 0 || !$this->supportsNestedTransactions()) {
            $this->transactionLevel++;
            return $this->pdo()->beginTransaction();
        }
        
        $this->pdo()->exec("SAVEPOINT LEVEL{$this->transactionLevel}");
        
        $this->transactionLevel++;
        
        return true;
    }
    
    /**
     * Commit a transaction.
     *
     * @return bool Returns true on success or false on failure.
     */
    public function commit(): bool
    {
        $this->transactionLevel--;
        
        if($this->transactionLevel === 0 || !$this->supportsNestedTransactions()) {
            return $this->pdo()->commit();
        }

        $this->pdo()->exec("RELEASE SAVEPOINT LEVEL{$this->transactionLevel}");

        return true;
    }

    /**
     * Rollback a transaction.
     *
     * @return bool Returns true on success or false on failure.
     */
    public function rollback(): bool
    {
        $this->transactionLevel--;

        if($this->transactionLevel === 0 || !$this->supportsNestedTransactions()) {
            return $this->pdo()->rollBack();
        }
        
        $this->pdo()->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transactionLevel}");
        
        return true;
    }
    
    /**
     * Returns true if supporting nested transactions, otherwise false.
     *
     * @return bool
     */
    public function supportsNestedTransactions(): bool
    {
        return in_array(
            $this->pdo()->getAttribute(PDO::ATTR_DRIVER_NAME),
            $this->supportsSavepoints
        );
    }
    
    /**
     * Execute a transaction.
     *
     * @param callable $callback
     * @return void
     * @throws Throwable
     */
    public function transaction(callable $callback): void
    {
        $this->begin();
        
        try {
            $callback($this);
            if ($this->pdo()->inTransaction()) {
                $this->commit();
            }
        } catch (Throwable $e) {
            if ($this->pdo()->inTransaction()) {
                $this->rollback();
            }
            throw $e;
        }
    }    
    
    /**
     * Returns the database name.
     *
     * @return string
     */    
    public function name(): string
    {
        return $this->name;
    }
    
    /**
     * Returns the connection.
     *
     * @return mixed
     */    
    public function connection(): mixed
    {
        return $this->pdo;
    }
    
    /**
     * Returns the value for the specified parameter name.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */    
    public function parameter(string $name, mixed $default = null): mixed
    {
        return $this->parameters[$name] ?? $default;
    }    
    
    /**
     * Returns the pdo.
     *
     * @return PDO
     */    
    public function pdo(): PDO
    {
        return $this->pdo; 
    }    
    
    /**
     * Bind values.
     *
     * @param PDOStatement $statement
     * @param array $bindings The bindings
     * @return void
     */
    protected function bindValues(PDOStatement $statement, array $bindings): void
    {
        foreach($bindings as $key => $value)
        {
            $statement->bindValue(
                is_string($key) ? $key : $key+1,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * Prepares the bindings.
     *
     * @param array $bindings The bindings
     * @return array The bindings prepared.
     */
    protected function prepareBindings(array $bindings): array
    {
        foreach ($bindings as $key => $value)
        {
            if (is_bool($value))
            {
                $bindings[$key] = (int) $value;
            }
        }

        return $bindings;
    }    
}