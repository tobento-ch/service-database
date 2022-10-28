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
 * Table
 */
class Table
{ 
    /**
     * @var array<string, ColumnInterface>
     */
    protected array $columns = [];
    
    /**
     * @var array<string, IndexInterface>
     */
    protected array $indexes = [];    
    
    /**
     * @var null|ItemsInterface
     */
    protected null|ItemsInterface $items = null;
    
    /**
     * @var int
     */
    protected int $itemsCount = 0;
    
    /**
     * @var bool
     */
    protected bool $truncate = false;

    /**
     * @var null|string
     */
    protected null|string $rename = null;
    
    /**
     * @var bool
     */
    protected bool $drop = false;    
    
    /**
     * @var array<string, mixed>
     */
    protected array $parameters = [];    
    
    /**
     * Create a new Table.
     *
     * @param string $name The name of the table.
     * @param ColumnInterface ...$columns
     */    
    public function __construct(
        protected string $name,
        ColumnInterface ...$columns
    ) {
        foreach($columns as $column) {
            $this->addColumn($column);
        }
    }

    /**
     * Returns the table name.
     *
     * @return string
     */    
    public function getName(): string
    {
        return $this->name;
    }
 
    /**
     * Set a name to rename the table.
     *
     * @param null|string $rename
     * @return static $this
     */    
    public function renameTable(null|string $rename): static
    {
        $this->rename = $rename;
        return $this;
    }
    
    /**
     * Returns the rename of the index if one.
     *
     * @return null|string
     */    
    public function getRename(): null|string
    {
        return $this->rename;
    }    
    
    /**
     * Adds a column.
     *
     * @param ColumnInterface $column
     * @return static $this
     */    
    public function addColumn(ColumnInterface $column): static
    {
        $this->columns[$column->getName()] = $column;
        return $this;
    }
    
    /**
     * Returns the table columns.
     *
     * @return array<string, ColumnInterface>
     */    
    public function getColumns(): array
    {
        return $this->columns;
    }
    
    /**
     * Returns the table column if exists.
     *
     * @return null|ColumnInterface
     */    
    public function getColumn(string $name): null|ColumnInterface
    {
        return $this->columns[$name] ?? null;
    }
    
    /**
     * Adds a index.
     *
     * @param IndexInterface $index
     * @return static $this
     */    
    public function addIndex(IndexInterface $index): static
    {
        $this->indexes[$index->getName()] = $index;
        return $this;
    }
    
    /**
     * Returns the table indexes.
     *
     * @return array<string, IndexInterface>
     */    
    public function getIndexes(): array
    {
        return $this->indexes;
    }
    
    /**
     * Returns the table index if exists.
     *
     * @return null|IndexInterface
     */    
    public function getIndex(string $name): null|IndexInterface
    {
        return $this->indexes[$name] ?? null;
    }    

    /**
     * Adds table items.
     *
     * @param null|iterable $items
     * @return ItemsInterface
     */    
    public function items(null|iterable $items): null|ItemsInterface
    {
        return $this->items = new Items(iterable: $items);
    }
 
    /**
     * Returns the table items.
     *
     * @return null|ItemsInterface
     */    
    public function getItems(): null|ItemsInterface
    {    
        return $this->items;
    }
    
    /**
     * Set the table items count
     *
     * @param int $itemsCount
     * @return static $this
     */    
    public function itemsCount(int $itemsCount): static
    {
        $this->itemsCount = $itemsCount;
        return $this;
    }
    
    /**
     * Returns the table items count.
     *
     * @return int
     */    
    public function getItemsCount(): int
    {
        return $this->itemsCount;
    }    
    
    /**
     * Add a parameter.
     *
     * @param string $name The name
     * @param mixed $value The value
     * @return static $this
     */
    public function parameter(string $name, mixed $value): static
    {        
        $this->parameters[$name] = $value;
        return $this;
    }
    
    /**
     * Returns the parameter value.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */    
    public function getParameter(string $name, mixed $default = null): mixed
    {
        return $this->parameters[$name] ?? $default;
    }
    
    /**
     * Adds a primary column.
     *
     * @param string $name
     * @return PrimaryColumn
     */    
    public function primary(string $name): PrimaryColumn
    {
        return $this->addedColumn(new PrimaryColumn($name));
    }
    
    /**
     * Adds a big primary column.
     *
     * @param string $name
     * @return BigPrimaryColumn
     */    
    public function bigPrimary(string $name): BigPrimaryColumn
    {
        return $this->addedColumn(new BigPrimaryColumn($name));
    }
    
    /**
     * Adds a bool column.
     *
     * @param string $name
     * @return BoolColumn
     */    
    public function bool(string $name): BoolColumn
    {
        return $this->addedColumn(new BoolColumn($name));
    }    

    /**
     * Adds a int column.
     *
     * @param string $name
     * @param int $length
     * @return IntColumn
     */    
    public function int(string $name, int $length = 11): IntColumn
    {
        return $this->addedColumn(new IntColumn($name, $length));
    }
    
    /**
     * Adds a tinyInt column.
     *
     * @param string $name
     * @param int $length
     * @return TinyIntColumn
     */    
    public function tinyInt(string $name, int $length = 1): TinyIntColumn
    {
        return $this->addedColumn(new TinyIntColumn($name, $length));
    }
    
    /**
     * Adds a bigInt column.
     *
     * @param string $name
     * @param int $length
     * @return BigIntColumn
     */    
    public function bigInt(string $name, int $length = 20): BigIntColumn
    {
        return $this->addedColumn(new BigIntColumn($name, $length));
    }    

    /**
     * Adds a char column.
     *
     * @param string $name
     * @param int $length
     * @return CharColumn
     */    
    public function char(string $name, int $length = 255): CharColumn
    {
        return $this->addedColumn(new CharColumn($name, $length));
    }
    
    /**
     * Adds a string column.
     *
     * @param string $name
     * @param int $length
     * @return StringColumn
     */    
    public function string(string $name, int $length = 255): StringColumn
    {
        return $this->addedColumn(new StringColumn($name, $length));
    }
    
    /**
     * Adds a text column.
     *
     * @param string $name
     * @return TextColumn
     */    
    public function text(string $name): TextColumn
    {
        return $this->addedColumn(new TextColumn($name));
    }
    
    /**
     * Adds a double column.
     *
     * @param string $name
     * @return DoubleColumn
     */    
    public function double(string $name): DoubleColumn
    {
        return $this->addedColumn(new DoubleColumn($name));
    }
    
    /**
     * Adds a float column.
     *
     * @param string $name
     * @return FloatColumn
     */    
    public function float(string $name): FloatColumn
    {
        return $this->addedColumn(new FloatColumn($name));
    }
    
    /**
     * Adds a decimal column.
     *
     * @param string $name
     * @param int $precision
     * @param int $scale     
     * @return DecimalColumn
     */    
    public function decimal(string $name, int $precision = 10, int $scale = 0): DecimalColumn
    {
        return $this->addedColumn(new DecimalColumn($name, $precision, $scale));
    }

    /**
     * Adds a datetime column.
     *
     * @param string $name
     * @return DatetimeColumn
     */    
    public function datetime(string $name): DatetimeColumn
    {
        return $this->addedColumn(new DatetimeColumn($name));
    }
    
    /**
     * Adds a date column.
     *
     * @param string $name
     * @return DateColumn
     */    
    public function date(string $name): DateColumn
    {
        return $this->addedColumn(new DateColumn($name));
    }
    
    /**
     * Adds a time column.
     *
     * @param string $name
     * @return TimeColumn
     */    
    public function time(string $name): TimeColumn
    {
        return $this->addedColumn(new TimeColumn($name));
    }
    
    /**
     * Adds a timestamp column.
     *
     * @param string $name
     * @return TimestampColumn
     */    
    public function timestamp(string $name): TimestampColumn
    {
        return $this->addedColumn(new TimestampColumn($name));
    }
    
    /**
     * Adds a json column.
     *
     * @param string $name
     * @return JsonColumn
     */    
    public function json(string $name): JsonColumn
    {
        return $this->addedColumn(new JsonColumn($name));
    }    
    
    /**
     * Adds a rename column.
     *
     * @param string $from
     * @param string $to
     * @return RenameColumn
     */    
    public function renameColumn(string $from, string $to): RenameColumn
    {
        return $this->addedColumn(new RenameColumn($from, $to));
    }
    
    /**
     * Adds a drop column.
     *
     * @param string $name
     * @return DropColumn
     */    
    public function dropColumn(string $name): DropColumn
    {
        return $this->addedColumn(new DropColumn($name));
    }
    
    /**
     * Adds a index.
     *
     * @param string $name
     * @return Index
     */    
    public function index(string $name = ''): Index
    {
        $index = new Index($name);
        $this->addIndex($index);
        return $index;
    }
    
    /**
     * Set whether to truncate the table.
     *
     * @param bool $truncate
     * @return static $this
     */    
    public function truncate(bool $truncate = true): static
    {
        $this->truncate = $truncate;
        return $this;
    }
    
    /**
     * Returns true if to truncate the table, otherwise false.
     *
     * @return bool
     */    
    public function truncating(): bool
    {
        return $this->truncate;
    }    

    /**
     * Set whether to drop the table.
     *
     * @param bool $drop
     * @return static $this
     */    
    public function dropTable(bool $drop = true): static
    {
        $this->drop = $drop;
        return $this;
    }
    
    /**
     * Returns true if to drop the table, otherwise false.
     *
     * @return bool
     */    
    public function dropping(): bool
    {
        return $this->drop;
    }    
    
    /**
     * Adds and returns the column.
     *
     * @param ColumnInterface $column
     * @return ColumnInterface
     */    
    protected function addedColumn(ColumnInterface $column): ColumnInterface
    {
        $this->addColumn($column);
        return $column;
    }    
}