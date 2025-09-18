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
use Tobento\Service\Database\Schema\ColumnInterface;
use Tobento\Service\Database\Schema\IndexInterface;
use Tobento\Service\Database\Schema\Lengthable;
use Tobento\Service\Database\Schema\Nullable;
use Tobento\Service\Database\Schema\Defaultable;
use Tobento\Service\Database\Schema\Unsignable;
use Tobento\Service\Database\Schema\RenameColumn;
use Tobento\Service\Database\Schema\DropColumn;
use Tobento\Service\Iterable\ChunkIterator;

class PdoSqliteGrammar implements GrammarInterface
{
    /**
     * @var array<string, string> From column type to mysql.
     */
    protected array $types = [
        'primary' => 'integer',
        'bigPrimary' => 'integer',
        'bool' => 'integer',
        'int' => 'integer',
        'tinyInt' => 'integer',
        'bigInt' => 'integer',
        'char' => 'char',
        'string' => 'varchar',
        'text' => 'text',
        'blob' => 'blob',
        'double' => 'double',
        'float' => 'float',
        'decimal' => 'decimal',
        'datetime' => 'datetime',
        'date' => 'date',
        'time' => 'time',
        'timestamp' => 'timestamp',
        'json' => 'longtext',
    ];
    
    /**
     * Create statements based on the specified tables.
     *
     * @param Table $table
     * @param null|Table $savedTable
     * @return Statements
     *
     * @throws GrammarException
     */
    public function createStatements(Table $table, null|Table $savedTable): Statements
    {
        return $this->buildStatements($table, $savedTable);
    }
    
    /**
     * Build statements based on the specified tables.
     *
     * @param Table $table
     * @param null|Table $savedTable
     * @return Statements
     *
     * @throws GrammarException
     */
    protected function buildStatements(Table $table, null|Table $savedTable): Statements
    {
        $statements = [];
        $new = [];
        $create = [];
        $update = [];
        $delete = [];
        $renamed = [];
        
        // Handle columns.
        foreach($table->getColumns() as $column) {
            
            if ($column instanceof RenameColumn) {

                $savedCol = $savedTable?->getColumn($column->getName());
                
                if (is_null($savedCol)) {
                    if ($savedTable?->getColumn($column->getNewName())) {
                        // column has been renamed already.
                        continue;
                    }
                    
                    throw new GrammarException(
                        'Cannot rename unknown column ['.$column->getName().']'
                    );
                }
                
                $column = $savedCol->withName($column->getNewName());
                $column->parameter('oldname', $savedCol->getName());
                $update[$column->getName()] = $column;
                $renamed[$savedCol->getName()] = $savedCol->getName();
                continue;
            }
            
            if ($column instanceof DropColumn) {
                
                $savedCol = $savedTable?->getColumn($column->getName());
                
                if (!is_null($savedCol)) {
                    $delete[$column->getName()] = $column;    
                }
                
                continue;
            }            
            
            if (is_null($savedTable)) {
                $create[$column->getName()] = $column;
                continue;
            }

            $savedColumn = $savedTable->getColumn($column->getName());

            if (is_null($savedColumn)) {
                $new[$column->getName()] = $column;
                continue;
            }
            
            // update column if different
            if (! $this->isColumnSame($savedColumn, $column)) {
                $update[$column->getName()] = $column;
            }
        }
                
        // build create table statement.
        if (!is_null($statement = $this->buildCreateTableStatement($create, $table))) {
            $statements[] = $statement;
        }
        
        // build update statments for update, rename and drop columns:
        if (!is_null($savedTable)) {
            foreach($this->buildUpdateColumnStatements($savedTable, $update, $delete, $table) as $statement) {
                $statements[] = $statement;
            }
        }
        
        // build new column statements.
        foreach($this->buildNewColumnStatements($new, $table) as $statement) {
            $statements[] = $statement;
        }

        // build indexes statements.
        foreach($this->buildIndexesStatements($table, $savedTable) as $statement) {
            $statements[] = $statement;
        }
        
        // build truncate table statement
        if (!is_null($statement = $this->buildTruncateTableStatement($table))) {
            if (!is_null($savedTable) || !empty($create)) {
                $statements[] = $statement;
            }
        }
        
        // build items statements.
        foreach($this->buildItemsStatements($table, $savedTable) as $statement) {
            $statements[] = $statement;
        }
        
        // build rename table statement
        if (!is_null($statement = $this->buildRenameTableStatement($table))) {
            if (is_null($savedTable) && empty($create)) {
                $table->renameTable(null);
            } else {
                $statements[] = $statement;
            }
        }
        
        // build drop table statement
        if (!is_null($statement = $this->buildDropTableStatement($table))) {
            $statements[] = $statement;
        }
        
        // handle columns for the new table.
        $columns = array_merge($new, $create, $update);
        $columnNames = array_keys(array_merge($columns, $delete, $renamed));
        
        if (!is_null($savedTable)) {
            foreach($savedTable->getColumns() as $savedColumn) {
                if (!in_array($savedColumn->getName(), $columnNames)) {
                    $columns[$savedColumn->getName()] = $savedColumn;
                }
            }
        }        
        
        // Create table based on the statements.
        $newTable = new Table(
            $table->getRename() ? $table->getRename() : $table->getName(),
            ...array_values($columns),
        );
        
        // add indexes to the new table: skip dropped indexes and handle renaming.
        foreach($table->getIndexes() as $index) {
            if ($index->dropping()) {
                continue;
            }

            if ($index->getRename()) {
                $newIndex = $savedTable
                    ?->getIndex($table->getName().'_'.$index->getName())
                    ?->withName($table->getName().'_'.$index->getRename());
                
                $newIndex = $newIndex ?: $index->withName($table->getName().'_'.$index->getRename());
                
                $newTable->addIndex($newIndex);
            } else {
                $newTable->addIndex($index);
            }
        }
        
        // set drop table so as storage knows if table has been dropped.
        if ($table->dropping()) {
            $newTable->dropTable(true);
        }
        
        return new Statements($statements, $newTable);
    }
    
    /**
     * Returns the built create table statement based on the specified columns.
     *
     * @param array<string, ColumnInterface> $columns
     * @param Table $table
     * @return null|Statement
     *
     * @throws GrammarException
     */
    protected function buildCreateTableStatement(array $columns, Table $table): null|Statement
    {
        if (empty($columns)) {
            return null;
        }

        $compiledColumns = [];
        
        foreach($columns as $column) {
            $compiledColumns[] = $this->compileColumn($column, $table);
        }
        
        $segments = [
            'CREATE TABLE IF NOT EXISTS '.$this->backtickValue($table->getName()).' (',
            implode(',', $compiledColumns),
            ')',
        ];
        
        return new Statement(
            statement: implode('', $segments),
            bindings: [],
            transactionable: false
        );
    }
    
    /**
     * Returns the built new column statements based on the specified columns.
     *
     * @param array<string, ColumnInterface> $columns
     * @param Table $table
     * @return array<int, Statement>
     *
     * @throws GrammarException
     */
    protected function buildNewColumnStatements(array $columns, Table $table): array
    {
        $statements = [];
        
        foreach($columns as $column) {
            $segments = [
                'ALTER TABLE '.$this->backtickValue($table->getName()).' ',
                'ADD COLUMN ',
                $this->compileColumn($column, $table),
            ];
            
            $statements[] = new Statement(
                statement: implode('', $segments),
                bindings: [],
                transactionable: true
            );             
        }

        return $statements;
    }
    
    /**
     * Returns the built update column statements based on the specified columns.
     *
     * @param array<string, ColumnInterface> $savedColumns
     * @param array<string, ColumnInterface> $updateColumns
     * @param array<string, ColumnInterface> $deleteColumns
     * @param Table $table
     * @return array<int, Statement>
     * @throws GrammarException
     */
    protected function buildUpdateColumnStatements(Table $savedTable, array $updateColumns, array $deleteColumns, Table $table): array
    {
        if (empty($updateColumns) && empty($deleteColumns)) {
            return [];
        }
        
        $tableTmp = $table->withName($table->getName().'_tmp');
        
        $statements = [];
        $create = [];
        $columnNames = [];
        
        // drop:
        foreach($savedTable->getColumns() as $column) {
            if (isset($deleteColumns[$column->getName()])) {
                continue;
            }
            
            $create[$column->getName()] = $column;
            $columnNames[$column->getName()] = $column->getName();
        }
        
        // rename or update:
        foreach($updateColumns as $column) {
            
            if ($column->getParameter('oldname')) {
                unset($create[$column->getParameter('oldname')]);
                unset($columnNames[$column->getParameter('oldname')]);
                $columnNames[$column->getName()] = $column->getParameter('oldname');
            }
            
            $create[$column->getName()] = $column;
        }
        
        // Step 1: Create new table with changes:
        if (!is_null($statement = $this->buildCreateTableStatement($create, $tableTmp))) {
            $statements[] = $statement;
        }
        
        // Step 2: Copy data
        // INSERT INTO products_new (id, name, price) SELECT id, name, price FROM products;
        $statements[] = new Statement(
            statement: implode(' ', [
                'INSERT INTO '.$this->backtickValue($tableTmp->getName()),
                '('.implode(', ', array_keys($columnNames)).')',
                'SELECT '.implode(', ', array_values($columnNames)),
                'FROM '.$this->backtickValue($table->getName()),
            ]),
            bindings: [],
            transactionable: false
        );
        
        // Step 3: Drop old table
        $statements[] = new Statement(
            statement: 'DROP TABLE IF EXISTS '.$this->backtickValue($table->getName()),
            bindings: [],
            transactionable: false
        );
        
        // Step 4: Rename tmp table
        $statements[] = new Statement(
            statement: 'ALTER TABLE '.$this->backtickValue($tableTmp->getName()).' RENAME TO '.$this->backtickValue($table->getName()),
            bindings: [],
            transactionable: false
        );
        
        // Step 5: Add indexes
        foreach($savedTable->getIndexes() as $index) {
            //CREATE UNIQUE INDEX index_int ON cats (int);
            //CREATE UNIQUE INDEX index_name ON table_name (column1, column2);
            $segments = [
                'CREATE',
            ];
            
            if ($index->isUnique()) {
                $segments[] = 'UNIQUE INDEX';
            } else {
                $segments[] = 'INDEX';
            }
            
            $segments[] = $this->backtickValue($index->getName());
            
            $segments[] = 'ON '.$this->backtickValue($table->getName());
            
            $columns = array_map(function($column) use ($columnNames) {
                $names = array_flip($columnNames);
                return $this->backtickValue($names[$column] ?? $column);
            }, $index->getColumns());
            
            $segments[] = '('.implode(',', $columns).')';
            
            $statements[] = new Statement(
                statement: implode(' ', $segments),
                bindings: [],
                transactionable: true
            );
        }
        
        return $statements;
    }
    
    /**
     * Returns the built delete column statements based on the specified columns.
     *
     * @param array<string, ColumnInterface> $columns
     * @param Table $table
     * @return array<int, Statement>
     *
     * @throws GrammarException
     */
    protected function buildDeleteColumnStatements(array $columns, Table $table): array
    {
        $statements = [];
        
        foreach($columns as $column) {
            $segments = [
                'ALTER TABLE '.$this->backtickValue($table->getName()),
                ' DROP COLUMN '.$this->backtickValue($column->getName()),
            ];
            
            $statements[] = new Statement(
                statement: implode('', $segments),
                bindings: [],
                transactionable: true
            );             
        }

        return $statements;
    }
    
    /**
     * Returns the built indexes statements.
     *
     * @param Table $table
     * @param null|Table $savedTable
     * @return array<int, Statement>
     *
     * @throws GrammarException
     */
    protected function buildIndexesStatements(Table $table, null|Table $savedTable): array
    {
        $statements = [];
        
        foreach($table->getIndexes() as $index) {
            // check if index columns exists.
            if (! $this->tableHasIndexColumns($index, $table, $savedTable)) {
                $index->drop(true);
                continue;
            }
            
            // rename index for uniqueness:
            $indexOld = $index;
            $index = $index->withName($table->getName().'_'.$index->getName());
            
            if ($indexOld->getRename()) {
                $index->rename($table->getName().'_'.$indexOld->getRename());
            }
            
            // drop index if dropping or renaming.
            if (!$index->isPrimary() && ($index->dropping() || $index->getRename())) {

                // skip if index does not exist.
                if (is_null($savedTable?->getIndex($index->getName()))) {
                    continue;
                }
                
                $statements[] = new Statement(
                    statement: 'DROP INDEX '.$this->backtickValue($index->getName()),
                    bindings: [],
                    transactionable: true
                );                
                
                if (is_null($index->getRename())) {
                    continue;
                }
            }
            
            // If index name already exist, skip it.
            if (
                !is_null($savedTable?->getIndex($index->getName()))
                && is_null($index->getRename())
            ) {
                continue;
            }

            if (!is_null($index->getRename())) {
                $index = $savedTable->getIndex($index->getName())->withName($index->getRename());
            }
            
            //CREATE UNIQUE INDEX index_int ON cats (int);
            //CREATE UNIQUE INDEX index_name ON table_name (column1, column2);
            $segments = [
                'CREATE',
            ];
            
            if ($index->isUnique()) {
                $segments[] = 'UNIQUE INDEX';
            } else {
                $segments[] = 'INDEX';
            }
            
            $segments[] = $this->backtickValue($index->getName());
            
            $segments[] = 'ON '.$this->backtickValue($table->getName());
            
            $columns = array_map(function($column) {
                return $this->backtickValue($column);
            }, $index->getColumns());
            
            $segments[] = '('.implode(',', $columns).')';
            
            $statements[] = new Statement(
                statement: implode(' ', $segments),
                bindings: [],
                transactionable: true
            );
        }
        
        return $statements;
    }
    
    /**
     * Returns the built truncate table statement.
     *
     * @param Table $table
     * @return null|Statement
     *
     * @throws GrammarException
     */
    protected function buildTruncateTableStatement(Table $table): null|Statement
    {
        if (! $table->truncating()) {
            return null;
        }

        return new Statement(
            statement: 'DELETE FROM '.$this->backtickValue($table->getName()),
            bindings: [],
            transactionable: true
        );
    }
    
    /**
     * Returns the built rename table statement.
     *
     * @param Table $table
     * @return null|Statement
     *
     * @throws GrammarException
     */
    protected function buildRenameTableStatement(Table $table): null|Statement
    {
        if (is_null($table->getRename())) {
            return null;
        }

        $segments = [
            'ALTER TABLE '.$this->backtickValue($table->getName()),
            ' RENAME TO '.$this->backtickValue($table->getRename()),
        ];
        
        return new Statement(
            statement: implode('', $segments),
            bindings: [],
            transactionable: true
        );
    }    
    
    /**
     * Returns the built drop table statement.
     *
     * @param Table $table
     * @return null|Statement
     *
     * @throws GrammarException
     */
    protected function buildDropTableStatement(Table $table): null|Statement
    {
        if (! $table->dropping()) {
            return null;
        }

        return new Statement(
            statement: 'DROP TABLE IF EXISTS '.$this->backtickValue($table->getName()),
            bindings: [],
            transactionable: false
        );
    }    

    /**
     * Returns the built items statements.
     *
     * @param Table $table
     * @param null|Table $savedTable
     * @return array<int, Statement>
     *
     * @throws GrammarException
     */
    protected function buildItemsStatements(Table $table, null|Table $savedTable): array
    {
        if (is_null($table->getItems())) {
            return [];
        }
        
        // if not forcing insert, insert only if there are not items yet.
        if (
            ! $table->getItems()->forcingInsert()
            && (!is_null($savedTable) && $savedTable->getItemsCount() > 0)
        ) {
            return [];
        }
        
        $chunks = new ChunkIterator($table->getItems(), $table->getItems()->getChunkLength());
        
        $statements = [];
        
        foreach($chunks as $items) {
            $statements[] = $this->compileInsertStatement($table, $items);        
        }

        return $statements;
    }

    /**
     * Compile insert statement for the given item.
     *
     * @param Table $table
     * @param array $items
     * @return Statement
     */
    protected function compileInsertStatement(Table $table, array $items): Statement
    {
        $firstItem = $items[array_key_first($items)];
        
        $columns = [];
        
        foreach(array_keys($firstItem) as $column) {
            $columns[] = $this->backtickValue($column);
        }
        
        $itemValues = '('.implode(', ', array_fill(0, count($columns), '?')).'),';
        $values = str_repeat($itemValues, count($items));
        $values = rtrim($values, ',');
        
        $bindings = [];
        
        foreach($items as $item) {
            $bindings[] = array_values($item);
        }
        
        $bindings = array_merge([], ...$bindings);
        
        // INSERT INTO table (col1, col2, col3) VALUES (?, ?, ?)
        $statement = 'INSERT INTO ';
        $statement .= $this->backtickValue($table->getName()).' ';
        $statement .= '('. implode(',', $columns).') ';
        $statement .= 'VALUES ';
        $statement .= $values;

        return new Statement(
            statement: $statement,
            bindings: $bindings,
            transactionable: $table->getItems()->withTransaction(),
        );
    }
    
    /**
     * Returns true if teble has index columns, otherwise false.
     *
     * @param IndexInterface $index
     * @param Table $table
     * @param null|Table $savedTable
     * @return bool
     */
    protected function tableHasIndexColumns(IndexInterface $index, Table $table, null|Table $savedTable): bool
    {        
        $columnNames = [];
        
        foreach($table->getColumns() as $column) {
            if (! $column instanceof DropColumn) {
                $columnNames[] = $column->getName();
            }            
        }
                
        if (!is_null($savedTable)) {
            foreach($table->getColumns() as $column) {
                $columnNames[] = $column->getName();  
            }
        }
        
        foreach($index->getColumns() as $columnName) {
            if (!in_array($columnName, $columnNames)) {
                return false;
            }
        }
        
        return true;
    }    
    
    /**
     * Compile column.
     *
     * @param ColumnInterface $column
     * @param Table $table
     * @return string
     *
     * @throws GrammarException
     */
    protected function compileColumn(ColumnInterface $column, Table $table): string
    {
        $type = $this->types[$column->getType()] ?? null;

        if (is_null($type)) {
            throw new GrammarException('Unsupported column type ['.$column->getType().']');
        }
        
        $clause = [];
        $clause[] = $this->backtickValue($column->getName());
        $clause[] = $type;
        
        if ($column instanceof Nullable) {
            $clause[] = $column->isNullable() ? 'NULL' : 'NOT NULL';
        }
        
        if (
            $column instanceof Defaultable
        ) {
            if (strtolower((string)$column->getDefault()) === 'null') {
                $clause[] = 'DEFAULT NULL';
            } else {
                if (is_numeric($column->getDefault())) {
                    $clause[] = 'DEFAULT '.(string)$column->getDefault();
                } elseif (is_scalar($column->getDefault())) {
                    $clause[] = 'DEFAULT \''.(string)$column->getDefault().'\'';
                }
            }
        }
        
        // can we set here again not only on create table ????
        if (in_array($column->getType(), ['primary', 'bigPrimary'])) {
            $clause[] = 'PRIMARY KEY'; // AUTOINCREMENT
        }
        
        if (in_array($column->getType(), ['json'])) {
            $clause[] = 'CHECK (json_valid('.$column->getName().'))';
        }
        
        return implode(' ', $clause);
    }
    
    /**
     * Returns whether the columns are same or not.
     *
     * @param ColumnInterface $old
     * @param ColumnInterface $new
     * @return bool
     */
    protected function isColumnSame(ColumnInterface $old, ColumnInterface $new): bool
    {
        if ($old::class !== $new::class) {
            return false;
        }
        
        if (
            $old instanceof Nullable
            && $new instanceof Nullable
            && ($old->isNullable() !== $new->isNullable())
        ) {
            return false;
        }
        
        if (
            $old instanceof Defaultable
            && $new instanceof Defaultable
            && ($old->getDefault() !== $new->getDefault())
        ) {
            return false;
        }
        
        return true;
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