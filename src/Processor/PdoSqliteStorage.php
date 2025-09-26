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

use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Database\Schema\ColumnInterface;
use Tobento\Service\Database\Schema\IndexInterface;
use Tobento\Service\Database\Schema\Index;
use Tobento\Service\Database\Schema\Lengthable;
use Tobento\Service\Database\Schema\Nullable;
use Tobento\Service\Database\Schema\Defaultable;
use Tobento\Service\Database\Schema\Unsignable;
use PDO;
use PDOException;

class PdoSqliteStorage implements StorageInterface
{
    /**
     * @var null|Table
     */
    protected null|Table $table = null;
    
    /**
     * @var array<string, string> From mysql to column type.
     */
    protected array $types = [
        'integer' => 'int',
        'tinyint' => 'tinyInt',
        'bigint' => 'bigInt',
        'char' => 'char',
        'varchar' => 'string',
        'text' => 'text',
        'longtext' => 'text',
        'blob' => 'blob',
        'double' => 'double',
        'float' => 'float',
        'decimal' => 'decimal',
        'datetime' => 'datetime',
        'date' => 'date',
        'time' => 'time',
        'timestamp' => 'timestamp',
    ];    

    /**
     * Returns true if the storage supports the database, otherwise false.
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
        
        return in_array($driver, ['sqlite']);
    }
    
    /**
     * Returns the specified table if exist, otherwise null.
     *
     * @param DatabaseInterface $database
     * @param string $table The table name
     * @return null|Table
     * @throws StorageFetchException
     *
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function fetchTable(DatabaseInterface $database, string $table): null|Table
    {
        if (! $this->supportsDatabase($database)) {
            throw new StorageFetchException('Unsupported Database or Driver');
        }
        
        try {            
            $statement = $database->pdo()->query('PRAGMA table_info('.$this->backtickValue($table).')');
            $columns = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // return null if table does not exist.
            return null;
        }
        
        if (empty($columns)) {
            return null;
        }
        
        $table = new Table($table);
        
        foreach($columns as $column) {
            $table->addColumn($this->createColumn($column));
        }

        foreach($this->createIndexes($database->pdo(), $table->getName()) as $index) {
            $table->addIndex($index);
        }
        
        $statement = $database->pdo()->query(
            'SELECT COUNT(*) AS number FROM '.$this->backtickValue($table->getName())
        );
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $table->itemsCount((int)($result['number'] ?? 0));

        return $table;    
    }
    
    /**
     * Store the table.
     *
     * @param DatabaseInterface $database
     * @param Table $table
     * @return void
     * @throws StorageStoreException
     */
    public function storeTable(DatabaseInterface $database, Table $table): void
    {
        // ignore
    }
    
    /**
     * Returns the created column.
     *
     * @param array $data
     * @return ColumnInterface
     */
    protected function createColumn(array $data): ColumnInterface
    {        
        $type = $this->resolveType($data);
        
        if (is_null($type)) {
            throw new StorageFetchException('Unable to determine column type');
        }
        
        $name = $data['name'] ?? '';
        
        $table = $this->table ?: new Table('tmp');
        
        if (!method_exists($table, $type)) {
            throw new StorageFetchException('Unable to create column ['.$type.']');
        }
        
        $column = $table->{$type}($name);
        
        $length = $this->resolveLength($data['type']);
        
        if (!is_null($length) && $column instanceof Lengthable) {
            $column->length($length);
        }

        if ($column instanceof Nullable) {
            $isNull = ($data['notnull'] ?? 0) === 1 ? false : true;
            $column->nullable($isNull);
        }
        
        if ($column instanceof Unsignable) {
            $column->unsigned(false);
        }
        
        if ($column instanceof Defaultable) {
            $default = $data['dflt_value'] ?? null;
            $column->default($default);
        }
        
        return $column;
    }
    
    /**
     * Returns the resolved type or null if not resolvable.
     *
     * @param array $data
     * @return null|string
     */
    protected function resolveType(array $data): null|string
    {
        $type = $data['type'] ?? null;
            
        if (is_null($type) || !is_string($type)) {
            return null;
        }
        
        if ((bool)($data['pk'] ?? false)) {
            return 'primary';
        }
        
        $type = strtolower($type);
        
        if (str_contains($type, 'timestamp')) {
            return 'timestamp';
        }
        
        // type mapping (driver specific)        
        foreach($this->types as $from => $to) {
            if (str_starts_with($type, $from)) {
                return $to;
            }
        }
        
        return null;
    }
    
    /**
     * Returns the resolved length or null if none.
     *
     * @param string $string
     * @return null|int
     */
    protected function resolveLength(string $string): null|int
    {
        $hasLength = (preg_match('#(\d+)#', $string, $matches) === 1);
        
        if ($hasLength) {
            $length = ltrim($matches[0], '(');
            return (int) rtrim($length, ')');
        }
        
        return null;
    }
    
    /**
     * Returns the created table indexes.
     *
     * @param PDO $pdo
     * @param string $name The table name.
     * @return array<string, IndexInterface>
     */
    protected function createIndexes(PDO $pdo, string $name): array
    {
        $statement = $pdo->query('PRAGMA index_list('.$this->backtickValue($name).')');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        //echo '<pre>'; print_r($rows); exit;
        
        // SELECT sql FROM sqlite_master WHERE type = 'index' AND name = 'index_int'
        //$statement = $pdo->query("SELECT sql FROM sqlite_master");
        //$rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        //echo '<pre>'; print_r($rows); exit;
        
        $indexes = [];
        
        foreach($rows as $data) {
            $name = $data['name'];
            
            $index = $indexes[$name] ?? new Index($name);
            
            // handle columns.
            $columns = $index->getColumns();
            
            $statement = $pdo->query('PRAGMA index_info('.$this->backtickValue($name).')');
            
            foreach($statement->fetchAll(PDO::FETCH_ASSOC) as $info) {
                $columns[] = $info['name'];
            }

            $index->column(...$columns);
            
            // handle unique.
            $index->unique((bool)($data['unique'] ?? false));
            
            // handle primary.
            $index->primary(($data['origin'] ?? null) === 'pk');
            
            if ($index->isPrimary()) {
                unset($indexes[$index->getName()]);
                $name = $index->getColumns()[0];
                $index = $index->withName($name);
            }
            
            $indexes[$name] = $index;
        }
        
        return $indexes;       
    }
    
    /**
     * Backtick value.
     *
     * @param string $value
     * @return string
     */
    protected function backtickValue(string $value): string
    {
        return '`'.$value.'`';
    }
}