<?php

namespace Cosmic\Database\Driver;

use Cosmic\Database\Common\QueryResult;
use Cosmic\Database\Common\ConnectionString;

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
     * Perform a batch operation on this driver, using a prepared query with context data.
     * 
     * @param string $preparedQuery A prepared query statement to execute.
     * @param array $values An array containing all the associated values for this query.
     * 
     * @return QueryResult The result data-set from the database.
     * @throws QueryExecutionException If something goes wrong during execution.
     */
    public abstract function executeBatch(string $preparedQuery, array $values): QueryResult;
}
