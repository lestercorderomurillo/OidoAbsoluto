<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\ORM\Common;

use Cosmic\ORM\Exceptions\UnreacheableDatasourceException;

/**
 * A simple connection.
 */
class Connection
{
    /**
     * @var ConnectionString $connectionString The stored connection string.
     */
    private ConnectionString $connectionString;

    /** 
     * Set a new connection string.
     * @param ConnectionString $connection A valid connection string.
     * 
     * @return void
     */
    public function setConnectionString(ConnectionString $connectionString): void
    {
        $this->connectionString = $connectionString;
    }

    /**
     * Open the connection using the given connection string.
     * 
     * @return bool True if the connection is established, false otherwise.
     */
    public function openSocket(): bool
    {
        try {

            $host = $this->connectionString->getHost();
            $dbName = $this->connectionString->getDatasourceName();

            $this->pdo = new \PDO(
                "mysql:host=$host;dbname=$dbName",
                $this->connectionString->getUsername(),
                $this->connectionString->getPassword()
            );

            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {

            throw new UnreacheableDatasourceException($e->getMessage);
        }

        return $this->isConnected();
    }

    /**
     * Close the stablished connection. Clears the internal PDO object.
     * 
     * @return void
     */
    public function closeSocket(): void
    {
        $this->pdo = null;
    }

    /** 
     * Verify if the connection is established successfully.
     * 
     * @return bool True if the connection is established, false otherwise.
     */
    public function isConnected(): bool
    {
        return $this->pdo != null;
    }

    /** 
     * Return the connection stream.
     * 
     * @return \PDO|null The connection stream.
     */
    public function getStream()
    {
        return $this->pdo;
    }
}
