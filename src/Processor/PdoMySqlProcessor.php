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

use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\PdoDatabaseInterface;
use PDOException;
use PDO;
use Throwable;

/**
 * PdoMySqlProcessor
 */
class PdoMySqlProcessor implements ProcessorInterface
{
    /**
     * @var StorageInterface
     */
    protected StorageInterface $storage;
    
    /**
     * @var GrammarInterface
     */
    protected GrammarInterface $grammar;    
    
    /**
     * Create a new PdoMySqlProcessor.
     *
     * @param null|StorageInterface $storage = null
     */    
    public function __construct(
        null|StorageInterface $storage = null,
    ) {
        $this->storage = $storage ?: new PdoMySqlStorage();
        $this->grammar = new PdoMySqlGrammar;
    }

    /**
     * Returns true if the processor supports the database, otherwise false.
     *
     * @param DatabaseInterface $database
     * @return bool
     */
    public function supportsDatabase(DatabaseInterface $database): bool
    {        
        if (! $database instanceof PdoDatabaseInterface) {
            return false;
        }
        
        $driver = $database->pdo()->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        return in_array($driver, ['mysql']);        
    }
    
    /**
     * Process a table schema for the specified database.
     *
     * @param Table $table
     * @param DatabaseInterface $database
     * @return void
     *
     * @throws ProcessException
     *
     * @psalm-suppress UndefinedInterfaceMethod
     */    
    public function process(Table $table, DatabaseInterface $database): void
    {
        if (! $this->supportsDatabase($database)) {
            throw new ProcessException('Unsupported Database or Driver');
        }
        
        $table->parameter('engine', $database->parameter('engine', 'InnoDB'));
        $table->parameter('charset', $database->parameter('charset', 'utf8mb4'));
        $table->parameter('collation', $database->parameter('collation', 'utf8mb4_unicode_ci'));
        
        try {
            /*
            Some databases, including MySQL, automatically
            issue an implicit COMMIT when a database definition
            language (DDL) statement such as DROP TABLE or
            CREATE TABLE is issued within a transaction.
            The implicit COMMIT will prevent you from rolling back
            any other changes within the transaction boundary.
            Source: https://www.php.net/manual/en/pdo.begintransaction.php
            */
            
            $savedTable = $this->storage->fetchTable($database, $table->getName());
                
            $statements = $this->grammar->createStatements($table, $savedTable);
            
            // execute non transactionable statements first.
            foreach($statements->getStatements() as $statement)
            {
                if (!$statement->isTransactionable()) {
                     $database->execute($statement->getStatement(), $statement->getBindings());
                }
            }
            
            if ($table->dropping()) {
                $this->storage->storeTable($database, $statements->getTable());
                return;
            }
            
            // execute transactionable statements.
            $database->transaction(function(DatabaseInterface $db) use ($statements): void {
                
                foreach($statements->getStatements() as $statement)
                {
                    if ($statement->isTransactionable()) {
                         $db->execute($statement->getStatement(), $statement->getBindings());
                    }
                }
                
                $this->storage->storeTable($db, $statements->getTable());
            });
            
        } catch (StorageFetchException $e) {
            throw new ProcessException($e->getMessage(), 0, $e);
        } catch (StorageStoreException $e) {
            throw new ProcessException($e->getMessage(), 0, $e);
        } catch (GrammarException $e) {
            throw new ProcessException($e->getMessage(), 0, $e);
        } catch (PDOException $e) {
            throw new ProcessException($e->getMessage(), 0, $e);
        } catch (Throwable $e) {
            throw new ProcessException($e->getMessage(), 0, $e);
        }
    }   
}