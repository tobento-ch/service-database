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

/**
 * PdoMySqlGrammar
 */
class PdoMySqlGrammar implements GrammarInterface
{
    /**
     * @var array<string, string> From column type to mysql.
     */
    protected array $types = [
        'primary' => 'int',
        'bigPrimary' => 'bigint',
        'bool' => 'tinyint',
        'int' => 'int',
        'tinyInt' => 'tinyint',
        'bigInt' => 'bigint',
        'char' => 'char',
        'string' => 'varchar',
        'text' => 'text',
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
        foreach($table->getColumns() as $column)
        {
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
            
            // can only be set on creation, so skip them.
            if (in_array($column->getType(), ['primary', 'bigPrimary'])) {                
                continue;
            }            

            $savedColumn = $savedTable->getColumn($column->getName());

            if (is_null($savedColumn)) {
                $new[$column->getName()] = $column;
                continue;
            }
            
            // update column
            $update[$column->getName()] = $column;
        }
                
        // build create table statement.
        if (!is_null($statement = $this->buildCreateTableStatement($create, $table)))
        {
            $statements[] = $statement;
        }

        // build new column statements.
        foreach($this->buildNewColumnStatements($new, $table) as $statement)
        {
            $statements[] = $statement;
        }
        
        // build update column statements.
        foreach($this->buildUpdateColumnStatements($update, $table) as $statement)
        {
            $statements[] = $statement;
        }
        
        // build delete column statements.
        foreach($this->buildDeleteColumnStatements($delete, $table) as $statement)
        {
            $statements[] = $statement;
        }

        // build indexes statements.
        foreach($this->buildIndexesStatements($table, $savedTable) as $statement)
        {
            $statements[] = $statement;
        }
                
        // build truncate table statement
        if (!is_null($statement = $this->buildTruncateTableStatement($table)))
        {
            if (!is_null($savedTable) || !empty($create)) {
                $statements[] = $statement;
            }
        }
        
        // build items statements.
        foreach($this->buildItemsStatements($table, $savedTable) as $statement)
        {
            $statements[] = $statement;
        }
        
        // build rename table statement
        if (!is_null($statement = $this->buildRenameTableStatement($table)))
        {            
            if (is_null($savedTable) && empty($create)) {
                $table->renameTable(null);
            } else {
                $statements[] = $statement;
            }            
        }
        
        // build drop table statement
        if (!is_null($statement = $this->buildDropTableStatement($table)))
        {
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
        foreach($table->getIndexes() as $index)
        {
            if ($index->dropping()) {
                continue;
            }

            if ($index->getRename()) {
                $newIndex = $savedTable?->getIndex($index->getName())?->withName($index->getRename());
                
                $newIndex = $newIndex ?: $index->withName($index->getRename());
                
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
        $primaryKeys = [];
        
        foreach($columns as $column)
        {
            if (in_array($column->getType(), ['primary', 'bigPrimary'])) {            
                $primaryKeys[] = $column->getName();
            }
            
            $compiledColumns[] = $this->compileColumn($column, $table);
        }
        
        foreach($primaryKeys as $key => $primaryKey)
        {
            $primaryKeys[$key] = $this->backtickValue($primaryKey);
        }
        
        $compiledPrimaryKeys = '';
            
        if (!empty($primaryKeys)) {
            $compiledPrimaryKeys = ', PRIMARY KEY ('.implode(',', $primaryKeys).')';    
        }
        
        $segments = [
            'CREATE TABLE IF NOT EXISTS '.$this->backtickValue($table->getName()).' (',
            implode(',', $compiledColumns),
            $compiledPrimaryKeys,
            ')',
            ' ENGINE='.$table->getParameter('engine', 'InnoDB'),
            ' DEFAULT CHARSET='.$table->getParameter('charset', 'utf8mb4'),
            ' COLLATE='.$table->getParameter('collation', 'utf8mb4_unicode_ci'),
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
        
        foreach($columns as $column)
        {
            $segments = [
                'ALTER TABLE '.$this->backtickValue($table->getName()).' ',
                'ADD COLUMN ',
                $this->compileColumn($column, $table),
            ];
            
            $prevColumnName = $this->getPreviousColumn($column, $table)?->getName();
            
            if ($prevColumnName) {
                $segments[] = ' AFTER '.$this->backtickValue($prevColumnName);
            }
            
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
     * @param array<string, ColumnInterface> $columns
     * @param Table $table
     * @return array<int, Statement>
     *
     * @throws GrammarException
     */    
    protected function buildUpdateColumnStatements(array $columns, Table $table): array
    {
        $statements = [];
        
        foreach($columns as $column)
        {
            $segments = [
                'ALTER TABLE '.$this->backtickValue($table->getName()),
            ];
            
            $segments[] = ' CHANGE COLUMN ';
            
            if ($column->getParameter('oldname')) {
                $segments[] = $this->backtickValue($column->getParameter('oldname'));
            } else {
                $segments[] = $this->backtickValue($column->getName());   
            }

            $segments[] = ' '.$this->compileColumn($column, $table);        
            
            $statements[] = new Statement(
                statement: implode('', $segments),
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
        
        foreach($columns as $column)
        {
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
        
        foreach($table->getIndexes() as $index)
        {
            // check if inxex columns exists.
            if (! $this->tableHasIndexColumns($index, $table, $savedTable)) {
                $index->drop(true);
                continue;
            }
            
            // drop index if dropping or renaming.
            if ($index->dropping() || $index->getRename()) {
                // skip if index does not exist.
                if (is_null($savedTable?->getIndex($index->getName()))) {
                    continue;
                }
                
                $segments = [
                    'ALTER TABLE '.$this->backtickValue($table->getName()),
                ];
                
                $segments[] = ' DROP INDEX '.$this->backtickValue($index->getName());
                
                $statements[] = new Statement(
                    statement: implode('', $segments),
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
            
            $segments = [
                'ALTER TABLE '.$this->backtickValue($table->getName()),
            ];    
            
            // Compile index columns without verify columns.
            $compileColumns = $this->compileIndexColumns($index);
            
            if ($index->isPrimary()) {
                $segments[] = ' ADD PRIMARY KEY '.$compileColumns;

                $statements[] = new Statement(
                    statement: implode('', $segments),
                    bindings: [],
                    transactionable: true
                );
                
                continue;
            }
            
            if ($index->isUnique()) {
                $segments[] = ' ADD UNIQUE KEY';
            } else {
                $segments[] = ' ADD KEY';
            }
                        
            $segments[] = ' '.$this->backtickValue($index->getName());
            
            $segments[] = ' '.$compileColumns;
            
            $statements[] = new Statement(
                statement: implode('', $segments),
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
            statement: 'TRUNCATE '.$this->backtickValue($table->getName()),
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
            ' RENAME '.$this->backtickValue($table->getRename()),
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
        
        foreach($chunks as $items)
        {
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
     * Compile index columns.
     *
     * @param IndexInterface $index
     * @return string
     */    
    protected function compileIndexColumns(IndexInterface $index): string
    {        
        $columns = array_map(function($column) {
            return $this->backtickValue($column);
        }, $index->getColumns());
        
        return '('.implode(',', $columns).')';
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
        
        if ($column instanceof Lengthable) {
            $clause[] = $type.'('.(string)$column->getLength().')';
        } else {
            $clause[] = $type;
        }

        if ($column->getParameter('charset')) {
            $clause[] = 'CHARACTER SET '.$column->getParameter('charset');
        }
        
        if ($column->getParameter('collation')) {
            $clause[] = 'COLLATE '.$column->getParameter('collation');
        }
        
        if (
            $column instanceof Unsignable
            && $column->isUnsigned()
        ) {
            $clause[] = 'UNSIGNED';
        }
        
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
        
        if (in_array($column->getType(), ['primary', 'bigPrimary'])) {
            $clause[] = 'AUTO_INCREMENT';
        }
        
        if (in_array($column->getType(), ['bool', 'json'])) {
            $clause[] = 'COMMENT \'type:'.$column->getType().'\'';
        }        
            
        return implode(' ', $clause);
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
    
    /**
     * Returns the previous column if any.
     *
     * @param ColumnInterface $column
     * @param Table $table
     * @return null|ColumnInterface
     */    
    protected function getPreviousColumn(ColumnInterface $column, Table $table): null|ColumnInterface
    {
        $columns = array_values($table->getColumns());
        
        foreach($columns as $index => $col)
        {
            if ($column->getName() === $col->getName()) {
                return $columns[$index-1] ?? null;
            }
        }
        
        return null;
    }    
}