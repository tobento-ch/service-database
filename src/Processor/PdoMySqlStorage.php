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

/**
 * PdoMySqlStorage
 */
class PdoMySqlStorage implements StorageInterface
{
    /**
     * @var null|Table
     */
    protected null|Table $table = null;
    
    /**
     * @var array<string, string> From mysql to column type.
     */
    protected array $types = [
        'int' => 'int',
        'tinyint' => 'tinyInt',
        'bigint' => 'bigInt',
        'char' => 'char',
        'varchar' => 'string',
        'text' => 'text',
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
        
        return in_array($driver, ['mysql']);
    }
    
    /**
     * Returns the specified table if exist, otherwise null.
     *
     * @param DatabaseInterface $database
     * @param string $name The table name
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
            $statement = $database->pdo()->query('SHOW FULL COLUMNS FROM '.$this->backtickValue($table));
            $columns = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // return null if table does not exist.
            return null;
        }

        $table = new Table($table);
        
        foreach($columns as $column)
        {
            $table->addColumn($this->createColumn($column));
        }
        
        foreach($this->createIndexes($database->pdo(), $table->getName()) as $index)
        {
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
     * @param string $name
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
        
        $name = $data['Field'] ?? '';
        
        $table = $this->table ?: new Table('tmp');
        
        if (!method_exists($table, $type)) {
            throw new StorageFetchException('Unable to create column ['.$type.']');
        }
        
        $column = $table->{$type}($name);
        
        $length = $this->resolveLength($data['Type']);
        
        if (!is_null($length) && $column instanceof Lengthable) {
            $column->length($length);
        }
        
        if ($column instanceof Unsignable) {
            $column->unsigned($this->resolveIsUnsigned($data['Type']));
        }        

        if ($column instanceof Nullable) {
            $isNull = $data['Null'] ?? '';
            $column->nullable($isNull === 'YES');
        }
        
        if ($column instanceof Defaultable) {
            $default = $data['Default'] ?? null;
            $column->default($default);
        }
        
        if (isset($data['Collation'])) {
            $column->parameter('collation', $data['Collation']);
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
        $type = $data['Type'] ?? null;
            
        if (is_null($type) || !is_string($type)) {
            return null;
        }
        
        $key = $data['Key'] ?? '';
        
        if ($key === 'PRI') {
            return str_starts_with($type, 'bigint') ? 'bigPrimary' : 'primary';
        }
        
        // retrieve bool and json type from comment.
        $comment = $data['Comment'] ?? '';
        
        if (str_contains($comment, 'type:bool')) {
            return 'bool';
        }
        
        if (str_contains($comment, 'type:json')) {
            return 'json';
        }
        
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
     * Returns whether is unsigned.
     *
     * @param string $string
     * @return bool
     */    
    protected function resolveIsUnsigned(string $string): bool
    {        
        return str_contains($string, 'unsigned');
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
        $statement = $pdo->query('SHOW INDEXES FROM '.$this->backtickValue($name));
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $indexes = [];
        
        foreach($rows as $data)
        {
            $name = $data['Key_name'];
            
            if ($name === 'PRIMARY') {
                continue;
            }
            
            $index = $indexes[$name] ?? new Index($name);
            
            // handle columns.
            $columns = $index->getColumns();
            $columns[] = $data['Column_name'];
            $index->column(...$columns);
            
            // handle unique.
            $isUnique = ($data['Non_unique'] ?? 1) ? false : true;
            $index->unique($isUnique);
            
            // handle primary.
            // Ignore as only one can be primary.
            
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