<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\ORM\Abstracts;

use Cosmic\ORM\Common\PreparedQuery;
use Cosmic\ORM\Common\ConnectionString;
use Cosmic\ORM\Common\QueryResult;

/**
 * This class represents an abstract repository based on models. 
 */
abstract class Repository
{
    /**
     * @var Query[] $queryQueue A collection of query objects to be executed.
     */
    private array $queryQueue;

    /**
     * @var Driver $driver An abstract driver class used to manage the database specifics.
     */
    protected Driver $driver;

    /**
     * Constructor. Create a new database using a specified driver and database connection string.
     * 
     * @param Driver $driver The driver to use for this database.
     * @param ConnectionString $connectionString Database connection string.
     */
    public function __construct(Driver $driver, ConnectionString $connectionString)
    {
        $this->flushQueue();
        $this->setDriver($driver);
        $this->setConnectionString($connectionString);
    }

    /**
     * Set a new driver instance for this database.
     * 
     * @param Driver $driver The driver to use for this database.
     * @return void
     */
    public function setDriver(Driver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Set a new connection string for this database.
     * 
     * @param ConnectionString $connectionString Datasource connection string.
     * @return void
     */
    public function setConnectionString(ConnectionString $connectionString)
    {
        if ($this->driver != null) {
            $this->driver->getConnection()->setConnectionString($connectionString);
        }
    }

    /**
     * Flush the pending query queue memory buffers.
     * 
     * @return void
     */
    public function flushQueue()
    {
        $this->queryQueue = [];
    }

    /**
     * Find the entity in the database with a particular model. 
     * Will ensure that only entity is matched, if not, this will return null.
     * 
     * @param string $modelClass The model to look for.
     * @param array $where Specfic "where string" for the query.
     * @return Model|null A model instance, or null if not found.
     */
    public abstract function find(string $modelClass, array $where = []);

    /**
     * Check if the given model exists in the database.
     * 
     * @param string $modelClass The model to look for.
     * @param array $where Specfic "where string" for the query.
     * @return bool True if it exists, false otherwise.
     */
    public abstract function exists(string $modelClass, array $where);

    /**
     * Find all the entities in the database with a particular model. 
     * 
     * @param string $modelClass The model to look for.
     * @param array $where Specfic "where string" for the query.
     * @param string $append Can be any string.
     * @return Model[] A collection of models.
     */
    public abstract function findAll(string $modelClass, array $where = [], string $append = __EMPTY__);

    /**
     * Save the the given models into the database.
     * 
     * @param Models[]|Model $models The model(s) to store into the database.
     * 
     * @return void
     */
    public abstract function save($models, bool $forceInsert = false): void;

    /**
     * Delete the entity model from the database. If the model is not found, nothing will happen.
     * 
     * @param Models[]|Model $models The model to search and delete from the database.
     * @return void
     */
    public abstract function delete($models): void;

    /**
     * Execute the stored query queue and return all results (models, single model or null).
     * Return an empty array if there are no results from this query batch.
     * 
     * @return QueryResult[] The result data-set from the database.
     * @throws QueryExecutionException If something goes wrong during execution.
     */
    public function commit()
    {
        $results = [];
        $this->driver->getConnection()->openSocket();

        foreach ($this->queryQueue as $query) {
            $results[] = $this->driver->execute($query->getStatement(), $query->getData());
        }

        $this->driver->getConnection()->closeSocket();
        $this->queryQueue = [];

        return $results;
    }

    /**
     * Perform a custom query and return the results.
     * 
     * @param string $query A query statement.
     * @param array $data Context for the query.
     * @return QueryResult The result data-set from the database.
     */
    protected function performQuery(string $query, array $queryData = []): QueryResult
    {
        $this->addQueryToQueue($query, $queryData);
        return $this->commit()[0];
    }

    /**
     * Append a new query to the pending commit queue.
     * 
     * @param string $query A query statement.
     * @param array $data Context for the query.
     * @return void
     */
    protected function addQueryToQueue(string $query, array $data = []): void
    {
        $this->queryQueue[] = new PreparedQuery(trim($query), $data);
    }

    /**
     * Return the query replacement placeholder array for this model compiled.
     * Uses the class attributes to resolve the placeholder array.
     * 
     * @param array $data The input array.
     * @param bool $skipID If true, the placeholder will skip the id attribute.
     * @return string The placeholder array already compiled.
     */
    protected function getAttributesPlaceholders(array $data, bool $skipID = false): string
    {
        $attributes = [];

        foreach ($data as $property => $value) {
            if ($skipID) {
                if (strtolower($property) !== "id") {
                    $attributes[] = '`' . $property . '` = :' . $property;
                }
            } else {
                $attributes[] = '`' . $property . '` = :' . $property;
            }
        }

        return implode(", ", $attributes);
    }

    /**
     * Return the bindings with their respective values for this model.
     * 
     * @param array $data The input array.
     * @param bool $skipID If true, the placeholder will skip the id attribute.
     * @return array The attributes array.
     */
    public function getAttributesValues(array $data, bool $skipID = false): array
    {
        $attributes = [];

        foreach ($data as $property => $value) {

            if ($skipID) {
                if (strtolower($property) !== "id") {
                    $attributes[":" . $property] = $value;
                }
            } else {
                $attributes[":" . $property] = $value;
            }
        }

        return $attributes;
    }
}
