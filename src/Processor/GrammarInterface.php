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

/**
 * GrammarInterface
 */
interface GrammarInterface
{    
    /**
     * Create statements based on the specified tables.
     *
     * @param Table $table
     * @param null|Table $savedTable
     * @return Statements
     *
     * @throws GrammarException
     */    
    public function createStatements(Table $table, null|Table $savedTable): Statements;
}