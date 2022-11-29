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

namespace Tobento\Service\Database\Schema;

/**
 * ColumnFactory
 */
class ColumnFactory implements ColumnFactoryInterface
{
    /**
     * Create a new Column.
     *
     * @param string $type The column type such as 'bigInt'.
     * @param string $name The column name.
     * @return ColumnInterface
     * @throws CreateColumnException
     */
    public function createColumn(string $type, string $name): ColumnInterface
    {
        switch ($type) {
            case 'bigInt':
                return new BigIntColumn($name);
            case 'bigPrimary':
                return new BigPrimaryColumn($name);
            case 'bool':
                return new BoolColumn($name);
            case 'char':
                return new CharColumn($name);
            case 'date':
                return new DateColumn($name);
            case 'datetime':
                return new DatetimeColumn($name);
            case 'decimal':
                return new DecimalColumn($name);
            case 'double':
                return new DoubleColumn($name);
            case 'float':
                return new FloatColumn($name);
            case 'int':
                return new IntColumn($name);
            case 'json':
                return new JsonColumn($name);
            case 'primary':
                return new PrimaryColumn($name);
            case 'string':
                return new StringColumn($name);
            case 'text':
                return new TextColumn($name);
            case 'time':
                return new TimeColumn($name);
            case 'timestamp':
                return new TimestampColumn($name);
            case 'tinyInt':
                return new TinyIntColumn($name);
        }
        
        throw new CreateColumnException(
            ['type' => $type, 'name' => $name],
            'Invalid column type'
        );
    }
    
    /**
     * Create a new Column from array.
     *
     * @param array $column
     * @return ColumnInterface
     * @throws CreateColumnException
     */
    public function createColumnFromArray(array $column): ColumnInterface
    {
        if (!isset($column['type']) || !is_string($column['type'])) {
            throw new CreateColumnException($column, 'Missing or invalid column type');
        }
        
        if (!isset($column['name']) || !is_string($column['name'])) {
            throw new CreateColumnException($column, 'Missing or invalid column name');
        }
        
        $col = $this->createColumn($column['type'], $column['name']);
        
        // Lengthable
        if (
            isset($column['length'])
            && is_int($column['length'])
            && $col instanceof Lengthable
        ) {
            $col->length($column['length']);
        }
        
        // Nullable
        if (
            array_key_exists('nullable', $column)
            && is_bool($column['nullable'])
            && $col instanceof Nullable
        ) {
            $col->nullable($column['nullable']);
        }
        
        // Defaultable
        if (
            array_key_exists('default', $column)
            && $col instanceof Defaultable
        ) {
            if (is_array($column['default'])) {
                $column['default'] = json_encode($column['default']);
            }
            
            $col->default($column['default']);
        }
        
        // Unsignable
        if (
            array_key_exists('unsigned', $column)
            && is_bool($column['unsigned'])
            && $col instanceof Unsignable
        ) {
            $col->unsigned($column['unsigned']);
        }
        
        // Parameters
        if (
            array_key_exists('parameters', $column)
            && is_array($column['parameters'])
        ) {
            foreach($column['parameters'] as $name => $value) {
                if (is_string($name)) {
                    $col->parameter(name: $name, value: $value);
                }
            }
        }
        
        // Decimal specific
        if ($col instanceof DecimalColumn) {
            
            $precision = 10;
            $scale = 0;
            
            if (
                array_key_exists('precision', $column)
                && is_int($column['precision'])
            ) {
                $precision = $column['precision'];
            }
            
            if (
                array_key_exists('scale', $column)
                && is_int($column['scale'])
            ) {
                $scale = $column['scale'];
            }            
            
            $col->precision($precision, $scale);
        }
        
        return $col;
    }
}