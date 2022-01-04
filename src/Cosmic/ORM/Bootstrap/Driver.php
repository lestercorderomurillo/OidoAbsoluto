<?php

namespace Cosmic\ORM\Bootstrap;

use Cosmic\ORM\Common\ConnectionString;
use Cosmic\ORM\Common\QueryResult;

/**
 * Represents a driver for a database. This is the abstract implementation.
 */
abstract class Driver
{
    /**
     * @var \PDO $pdo PHP PDO object.
     */
    protected $pdo;

    /**
     * @var ConnectionString $connectionString The stored connection string.
     */
    protected ConnectionString $connectionString;

    /**
     * Set the current database connection string for this driver.
     * 
     * @param ConnectionString $connectionString The connectionString to store.
     * 
     * @return void
     */
    public function setConnectionString(ConnectionString $connectionString): void
    {
        $this->connectionString = $connectionString;
    }

    /**
     * Opens a new connection to the database using this driver and connection string.
     * 
     * @return void
     */
    public abstract function openConnection(): void;

    /**
     * Close this connection inmediately.
     * 
     * @return void
     */
    public abstract function closeConnection(): void;

    /**
     * Execute a single query operation on this driver, using a prepared query with context data.
     * Updates and insert queries will always return null as they don't need data from the database.
     * 
     * @param string $preparedQuery A secured query statement to execute.
     * @param array $values The values to be used in the prepared query.
     * 
     * @return QueryResult The query result from the database.
     * @throws QueryExecutionException If something goes wrong during execution.
     */
    public abstract function execute(string $preparedQuery, array $values): QueryResult;
}
