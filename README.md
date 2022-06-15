# Database Service

With the Database Service you can create and manage databases easily.

## Table of Contents

- [Getting started](#getting-started)
	- [Requirements](#requirements)
	- [Highlights](#highlights)
- [Documentation](#documentation)
	- [Databases](#databases)
        - [Create Databases](#create-databases)
        - [Add Databases](#add-databases)
        - [Get Database](#get-database)
        - [Default Databases](#default-databases)
    - [PDO Database](#pdo-database)
        - [Create PDO Database](#create-pdo-database)
        - [Pdo Database Factory](#pdo-database-factory)
        - [Using PDO Database](#using-pdo-database)
    - [Migration](#migration)
        - [Table Schema](#table-schema)
            - [Column Types](#column-types)
            - [Rename And Drop](#rename-and-drop)
            - [Column Parameters](#column-parameters)
            - [Indexes](#indexes)
            - [Foreign Keys](#foreign-keys)
            - [Items and Seeding](#items-and-seeding)
                - [Items](#items)
                - [Item Factory](#item-factory)
                - [Json File Items](#json-file-items)
        - [Processors](#processors)
            - [Pdo MySql Processor](#pdo-mysql-processor)
            - [Stack Processor](#stack-processor)
        - [Storages](#storages)
            - [Pdo MySql Storage](#pdo-mysql-storage)
            - [Stack Storage](#stack-storage)
            - [Custom Storage](#custom-storage)
        - [Security](#security)
        - [Migrator](#migrator)
- [Credits](#credits)
___

# Getting started

Add the latest version of the Database service project running this command.

```
composer require tobento/service-database
```

## Requirements

- PHP 8.0 or greater

## Highlights

- Framework-agnostic, will work with any project
- Decoupled design
- Managing Databases
- Simple PDO Database Wrapper
- Migration support with table schema builder and seeding items

# Documentation

## Databases

### Create Databases

```php
use Tobento\Service\Database\Databases;
use Tobento\Service\Database\DatabasesInterface;

$databases = new Databases();

var_dump($databases instanceof DatabasesInterface);
// bool(true)
```

### Add Databases

**add**

```php
use Tobento\Service\Database\Databases;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\DatabaseInterface;
use PDO;

$databases = new Databases();

$database = new PdoDatabase(
    pdo: new PDO('sqlite::memory:'),
    name: 'name',
);

var_dump($database instanceof DatabaseInterface);
// bool(true)

$databases->add($database);
```

**register**

You may use the register method to only create database if requested.

```php
use Tobento\Service\Database\Databases;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\DatabaseInterface;
use PDO;

$databases = new Databases();

$databases->register(
    'name',
    function(string $name): DatabaseInterface {        
        return new PdoDatabase(
            new PDO('sqlite::memory:'),
            $name
        );
    }
);
```

### Get Database

If the database does not exist or could not get created it throws a DatabaseException.

```php
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\DatabaseException;

$database = $databases->get('name');

var_dump($database instanceof DatabaseInterface);
// bool(true)

$databases->get('unknown');
// throws DatabaseException
```

You may use the **has** method to check if a database exists.

```php
var_dump($databases->has('name'));
// bool(false)
```

### Default Databases

You may add default databases for your application design.

```php
use Tobento\Service\Database\Databases;
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\DatabaseException;
use PDO;

$databases = new Databases();

$databases->add(
    new PdoDatabase(new PDO('sqlite::memory:'), 'sqlite')
);

// add default
$databases->addDefault(name: 'primary', database: 'sqlite');

// get default database for the specified name.
$primaryDatabase = $databases->default('primary');

var_dump($primaryDatabase instanceof DatabaseInterface);
// bool(true)

var_dump($databases->hasDefault('primary'));
// bool(true)

var_dump($databases->getDefaults());
// array(1) { ["primary"]=> string(6) "sqlite" }

$databases->default('unknown');
// throws DatabaseException
```

## PDO Database

### Create PDO Database

```php
use Tobento\Service\Database\PdoDatabase;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\PdoDatabaseInterface;
use PDO;

$database = new PdoDatabase(
    pdo: new PDO('sqlite::memory:'),
    name: 'sqlite',
);

var_dump($database instanceof DatabaseInterface);
// bool(true)

var_dump($database instanceof PdoDatabaseInterface);
// bool(true)
```

### Pdo Database Factory

You may use the factory to easily create a database.

**createDatabase**

```php
use Tobento\Service\Database\PdoDatabaseFactory;
use Tobento\Service\Database\DatabaseFactoryInterface;
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\DatabaseException;
use PDO;

$factory = new PdoDatabaseFactory();

var_dump($factory instanceof DatabaseFactoryInterface);
// bool(true)

$database = $factory->createDatabase(
    name: 'mysql',
    config: [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => null,
        'database' => 'db_name',
        'charset' => 'utf8mb4',
        'username' => 'root',
        'password' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
);
// throws DatabaseException on failure

var_dump($database instanceof PdoDatabaseInterface);
// bool(true)

var_dump($database instanceof DatabaseInterface);
// bool(true)

// with dsn parameter
$database = $factory->createDatabase(
    name: 'sqlite',
    config: [
        'dsn' => 'sqlite::memory:',
        'username' => '',
        'password' => '',
        'options' => [],
    ],
);
```

**createPdo**

```php
use Tobento\Service\Database\PdoDatabaseFactory;
use Tobento\Service\Database\PdoFactoryInterface;
use Tobento\Service\Database\DatabaseException;
use PDO;

$factory = new PdoDatabaseFactory();

var_dump($factory instanceof PdoFactoryInterface);
// bool(true)

$pdo = $factory->createPdo(
    name: 'sqlite',
    config: [
        'dsn' => 'sqlite::memory:',
        'username' => '',
        'password' => '',
        'options' => [],
    ],
);
// throws DatabaseException on failure

var_dump($pdo instanceof PDO);
// bool(true)
```

### Using PDO Database

Use the **execute** method to excute any prepared statements.

```php
use PDOStatement;

$statement = $database->execute(
    statement: 'ALTER TABLE products ADD color varchar(60)'
);

var_dump($statement instanceof PDOStatement);
// bool(true)
```

**select**

```php
$products = $database->execute(
    statement: 'SELECT title FROM products WHERE color = ?',
    bindings: ['blue']
)->fetchAll();

// with named parameters, order free
$products = $database->execute(
    'SELECT title FROM products WHERE color = :color',
    ['color' => 'blue']
)->fetchAll();
```

**insert**

```php
$database->execute(
    statement: 'INSERT INTO shop_products (name, color) VALUES (?, ?)',
    bindings: ['Shirt', 'blue'],
);

// you might return the number of rows affected:
$rowsAffected = $database->execute(
    'INSERT INTO shop_products (name, color) VALUES (?, ?)',
    ['Shirt', 'blue']
)->rowCount();

var_dump($rowsAffected);
// int(1)
```

**update**

Execute a insert statement returning the number of rows affected.

```php
$rowsAffected = $database->execute(
    statement: 'UPDATE products SET name = ? WHERE color = ?',
    bindings: ['Shirt', 'blue']
)->rowCount();

var_dump($rowsAffected);
// int(2)
```

**delete**

Execute a delete statement returning the number of rows affected.

```php
$rowsAffected = $database->execute(
    statement: 'DELETE FROM shop_products WHERE color = ?',
    bindings: ['blue']
)->rowCount();

var_dump($rowsAffected);
// int(2)
```

**transaction**

You may use the transaction method to run a set of database operations within a transaction. If an exception is thrown within the transaction closure, the transaction will automatically be rolled back. If the closure executes successfully, the transaction will automatically be committed.

```php
use Tobento\Service\Database\PdoDatabaseInterface;

$database->transaction(function(PdoDatabaseInterface $db): void {

    $db->execute(
        'UPDATE products SET active = ? WHERE color = ?',
        [true, 'red']
    );
    
    $db->execute(
        'UPDATE products SET active = ? WHERE color = ?',
        [false, 'bar']
    );    
});
```

**commit**

```php
$database->begin();

// your queries

$database->commit();
```

**rollback**

```php
$database->begin();

// your queries

$database->rollback();
```

**supportsNestedTransactions**

```php
var_dump($database->supportsNestedTransactions());
// bool(true)
```

**PDO**

```php
use PDO;

var_dump($database->pdo() instanceof PDO);
// bool(true)
```

## Migration

### Table Schema

Table schemas are used by [Processors](#processors) and [Storages](#storages) for migration processing.

```php
use Tobento\Service\Database\Schema\Table;

$table = new Table(name: 'products');
$table->primary('id');
$table->string('name', 100)->nullable(false)->default('');
$table->bool('active', true);
```

#### Column Types

**Available Types**

| Type | Parameters | Lengthable | Nullable | Defaultable | Unsignable | Description |
| --- | --- | --- | --- | --- | --- | --- |
| **primary** | name: 'column' | yes | no | no | yes | Usually mapped as int, auto-incrementing and added as primary index column. |
| **bigPrimary** | name: 'column' | yes | no | no | yes | Usually mapped as bigint, auto-incrementing and added as primary index column. |
| **bool** | name: 'column' | no | no | yes | no | Some databases will store it as tinyint (1/0). |
| **int** | name: 'column', length: 11 | yes | yes | yes | yes | - |
| **tinyInt** | name: 'column', length: 1 | yes | yes | yes | yes | - |
| **bigInt** | name: 'column', length: 20 | yes | yes | yes | yes | - |
| **char** | name: 'column', length: 255 | yes | yes | yes | no | - |
| **string** | name: 'column', length: 255 | yes | yes | yes | no | - |
| **text** | name: 'column' | no | yes | yes | no | - |
| **double** | name: 'column' | no | yes | yes | no | - |
| **float** | name: 'column' | no | yes | yes | no | - |
| **decimal** | name: 'column', precision: 10, scale:0 | no | yes | yes | no | - |
| **datetime** | name: 'column' | no | yes | yes | no | - |
| **date** | name: 'column' | no | yes | yes | no | - |
| **time** | name: 'column' | no | yes | yes | no | - |
| **timestamp** | name: 'column' | no | yes | yes | no | - |
| **json** | name: 'column' | no | yes | yes | no | - |

**Lengthable**

To set a column length.

```php
$table->string('name')->length(21);
```

**Nullable**

To set column as NOT NULL, use nullable method with false as parameter:

```php
$table->string('name')->nullable(false);
```

**Defaultable**

To set a default value for the column.

```php
$table->bool('name')->default(true);
```

**Unsignable**

To set column as UNSIGNED.

```php
$table->int('name')->unsigned(true);
```

**primary / bigPrimary**

Primary and bigPrimary columns will only be set while creation.

#### Rename and Drop

**renameColumn**

```php
$table->renameColumn('column', 'new_column');
```

**dropColumn**

```php
$table->dropColumn('column');
```

**renameTable**

```php
$table->renameTable('new_name');
```

**dropTable**

```php
$table->dropTable();
```

**truncate**

```php
$table->truncate();
```

#### Column Parameters

**charset**

You might set the charset of the column.

```php
$table->string('column')->parameter('charset', 'utf8mb4');
```

**collation**

You might set the collation of the column.

```php
$table->string('column')->parameter('collation', 'utf8mb4_roman_ci');
```

#### Indexes

**Simple index**

```php
$table->index('index_name')->column('name');
```

**Compound index**

```php
$table->index('index_name')->column('name', 'another_name');
```

**Simple unique index**

```php
$table->index('index_name')->column('name')->unique();
```

**Compound unique index**

```php
$table->index('index_name')->column('name', 'another_name')->unique();
```

**Primary index**

```php
$table->index()->column('name')->primary();
```

**Rename index**

```php
$table->index('index_name')->rename('new_name');
```

**Drop index**

```php
$table->index('index_name')->drop();
```

#### Foreign Keys

No supported yet! 

#### Items and Seeding

##### Items

```php
use Tobento\Service\Database\Schema\Items;

$table->items(new Items([
    ['name' => 'Foo', 'active' => true],
    ['name' => 'Bar', 'active' => true],
    // ...
]))
->chunk(length: 100)
->useTransaction(false) // default is true
->forceInsert(true); // default is false
```

**chunk**

You may play around with the chunk **length** parameter for speed improvements while having many items.

**useTransaction**

If set to **true**, it uses transaction while proccessing if the database supports it.

**forceInsert**

If set to **true**, it will always inserts the items, otherwise they will only be inserted if there there are not items yet.

##### Item Factory

You may use the item factory to seed items and use the [Seeder Service](https://github.com/tobento-ch/service-seeder) to generate fake data.

```php
use Tobento\Service\Database\Schema\ItemFactory;
use Tobento\Service\Seeder\Str;
use Tobento\Service\Seeder\Arr;

$table->items(new ItemFactory(function() {
    return [
        'name' => Str::string(10),
        'color' => Arr::item(['green', 'red', 'blue']),
    ];
}))
->create(number: 1000000) // create 1 million items
->chunk(length: 10000)
->useTransaction(false) // default is true
->forceInsert(true); // default is false
```

**create**

Define the number of items you want to create.

**chunk**

You may play around with the chunk **length** parameter for speed improvements while having many items.

**useTransaction**

If set to **true**, it uses transaction while proccessing if the database supports it.

**forceInsert**

If set to **true**, it will always inserts the items, otherwise they will only be inserted if there there are not items yet.

##### Json File Items

```php
use Tobento\Service\Database\Schema\JsonFileItems;

$table->items(new JsonFileItems(file: 'private/src/countries.json'))
      ->map(function($item) {
          return [
              'iso' => $item['iso'] ?? '',
              'name' => $item['country'] ?? '',
          ];
      })
      ->chunk(length: 100)
      ->useTransaction(true) // default is true
      ->forceInsert(false); // default is false
```

**chunk**

You may play around with the chunk **length** parameter for speed improvements while having many items.

**useTransaction**

If set to **true**, it uses transaction while proccessing if the database supports it.

**forceInsert**

If set to **true**, it will always inserts the items, otherwise they will only be inserted if there there are not items yet.


### Processors

Processors are used to process the table on the specified database.

#### Pdo MySql Processor

The proccessor will automatically determine if to add or modify table columns and indexes.

```php
use Tobento\Service\Database\Processor\PdoMySqlProcessor;
use Tobento\Service\Database\Processor\ProcessorInterface;
use Tobento\Service\Database\Processor\ProcessException;
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\Schema\Table;

$processor = new PdoMySqlProcessor();

var_dump($processor instanceof ProcessorInterface);
// bool(true)

try {
    $processor->process(
        $table, // Table
        $database // PdoDatabaseInterface
    );    
} catch (ProcessException $e) {
    // Handle exception.
}
```

You may create a [Custom Storage](#custom-storage) for the proccssor. The default storage [Pdo MySql Storage](#pdo-mysql-storage) will query the database to create the current table as to determine modifications.

```php
use Tobento\Service\Database\Processor\PdoMySqlProcessor;

$processor = new PdoMySqlProcessor(new CustomStorage());
```

#### Stack Processor

You may use the "stack processor" to support multiple databases. Only the first processor which supports the specified database will process the action.

```php
use Tobento\Service\Database\Processor\Processors;
use Tobento\Service\Database\Processor\PdoMySqlProcessor;
use Tobento\Service\Database\Processor\ProcessorInterface;
use Tobento\Service\Database\Processor\ProcessException;

$processors = new Processors(
    new PdoMySqlProcessor(),
);

var_dump($processor instanceof ProcessorInterface);
// bool(true)

try {
    $processors->process($table, $database);
} catch (ProcessException $e) {
    //
}
```

### Storages

Storages are used to fetch the current table or store the processed.

#### Pdo MySql Storage

**fetchTable**

The storage will query the database to create the current table.

```php
use Tobento\Service\Database\Processor\PdoMySqlStorage;
use Tobento\Service\Database\Processor\StorageInterface;
use Tobento\Service\Database\Processor\StorageFetchException;
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\Schema\Table;

$storage = new PdoMySqlStorage();

var_dump($storage instanceof StorageInterface);
// bool(true)

try {
    $table = $storage->fetchTable(
        $database, // PdoDatabaseInterface
        'table_name'
    );
    
    var_dump($table instanceof Table);
    // bool(true) or NULL if table does not exist.

} catch (StorageFetchException $e) {
    // Handle exception.
}
```

**storeTable**

No table data is stored as fetching will create the table.

```php
use Tobento\Service\Database\Processor\PdoMySqlStorage;
use Tobento\Service\Database\Processor\StorageInterface;
use Tobento\Service\Database\Processor\StorageStoreException;
use Tobento\Service\Database\PdoDatabaseInterface;
use Tobento\Service\Database\Schema\Table;

$storage = new PdoMySqlStorage();

var_dump($storage instanceof StorageInterface);
// bool(true)

try {
    $storage->storeTable(
        $database, // PdoDatabaseInterface
        $table // Table
    );

} catch (StorageStoreException $e) {
    // Handle exception.
}
```

#### Stack Storage

You may use the "stack storage" to support multiple databases. Only the first storage which supports the specified database will process the action.

```php
use Tobento\Service\Database\Processor\Storages;
use Tobento\Service\Database\Processor\PdoMySqlStorage;
use Tobento\Service\Database\Processor\StorageInterface;
use Tobento\Service\Database\Processor\StorageStoreException;

$storages = new Storages(
    new PdoMySqlStorage(),
);

var_dump($storages instanceof StorageInterface);
// bool(true)

try {
    $storages->storeTable($database, $table);
} catch (StorageStoreException $e) {
    // Handle exception.
}
```

#### Custom Storage

You may create custom storages for proccssors or as standalone usage.

```php
use Tobento\Service\Database\Processor\PdoMySqlProcessor;
use Tobento\Service\Database\Processor\StorageInterface;
use Tobento\Service\Database\Processor\StorageFetchException;
use Tobento\Service\Database\Processor\StorageStoreException;
use Tobento\Service\Database\Processor\ProcessException;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Database\DatabaseInterface;

class CustomStorage implements StorageInterface
{
    /**
     * Returns true if the processor supports the database, otherwise false.
     *
     * @param DatabaseInterface $database
     * @return bool
     */
    public function supportsDatabase(DatabaseInterface $database): bool
    {
        return true;
    }
    
    /**
     * Returns the specified table if exist, otherwise null.
     *
     * @param DatabaseInterface $database
     * @param string $name The table name
     * @return null|Table
     * @throws StorageFetchException
     */    
    public function fetchTable(DatabaseInterface $database, string $table): null|Table
    {
        // your logic.
        return null;
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
        // your logic.
    }
}

try {
    $table = (new CustomStorage())->fetchTable($database, 'table_name');
} catch (ProcessException $e) {
    // Handle exception.
}
```
### Security

> :warning: **Avoid using user provided data for creating [Table Schema](#table-schema) without a proper whitelist!**

### Migrator

You might install and use the [Migration Service](https://github.com/tobento-ch/service-migration) for migration processing.

```php
use Tobento\Service\Database\Migration\DatabaseAction;
use Tobento\Service\Database\Processor\ProcessorInterface;
use Tobento\Service\Database\DatabaseInterface;
use Tobento\Service\Database\Schema\Table;
use Tobento\Service\Migration\ActionInterface;

$action = new DatabaseAction(
    processor: $processor, // ProcessorInterface
    database: $database, // DatabaseInterface
    table: $table, // Table
    description: 'Database table migrated'
);

var_dump($action instanceof ActionInterface);
// bool(true)
```

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)