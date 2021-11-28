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

use PDOException;
use PDO;

/**
 * PdoDatabaseFactory
 */
class PdoDatabaseFactory implements DatabaseFactoryInterface, PdoFactoryInterface
{    
    /**
     * Create a new Database based on the configuration.
     *
     * @param string $name Any database name.
     * @param array $config Configuration data.
     * @return DatabaseInterface
     * @throws DatabaseException
     */    
    public function createDatabase(string $name, array $config = []): DatabaseInterface
    {
        $parameters = [];

        if (isset($config['engine'])) {
            $parameters[] = $config['engine'];
        }
        
        if (isset($config['charset'])) {
            $parameters[] = $config['charset'];
        }
        
        if (isset($config['collation'])) {
            $parameters[] = $config['collation'];
        }        
        
        return new PdoDatabase($this->createPdo($name, $config), $name, $parameters);
    }

    /**
     * Create a new PDO instance based on the configuration.
     *
     * @param string $name Any database name.
     * @param array $config Configuration data.
     * @return PDO
     * @throws DatabaseException
     */    
    public function createPdo(string $name, array $config = []): PDO
    {
        if (!isset($config['dsn']))
        {
            $config['driver'] ??= 'mysql';
            $config['host'] ??= '127.0.0.1';

            $dsn = $config['driver'].':host='.$config['host'].';';

            if (isset($config['port'])) {
                $dsn .= 'port='.$config['port'].';';
            }

            if (isset($config['database'])) {
                $dsn .= 'dbname='.$config['database'].';';
            }

            if (isset($config['charset'])) {
                $dsn .= 'charset='.$config['charset'].';';
            }
            
            $config['dsn'] = $dsn;
        }
        
        try {
            return new PDO(
                $config['dsn'],
                $config['username'] ?? '',
                $config['password'] ?? '',
                $config['options'] ?? []
            );
        } catch(PDOException $e) {
            throw new DatabaseException($name, 'Unable to connect ['.$name.'] database!', 0, $e);
        }
    }    
}