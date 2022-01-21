<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\ORM\Abstracts;

use Cosmic\ORM\Common\Connection;
use Cosmic\ORM\Common\QueryResult;

/**
 * Represents a datasource driver. This is the abstract implementation.
 */
abstract class Driver
{
    /** @var Connection $connection The internal connection. */
    private Connection $connection;

    /** Constructor.  */
    public function __construct()
    {
        $this->connection = new Connection();
    }

    /**
     * Return the current connection for this driver.
     * 
     * @return Connection The current connection instance.
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Execute a single query operation on this driver, using a prepared query with context data.
     * Updates and insert queries will always return null as they don't need data from the database.
     * 
     * @param string $preparedQuery A secured query statement to execute.
     * @param array $values The values to be used in the prepared query.
     * @return QueryResult The query result from the database.
     * @throws QueryExecutionException If something goes wrong during execution.
     */
    public abstract function execute(string $preparedQuery, array $values): QueryResult;
}
