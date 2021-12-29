<?php

namespace Cosmic\Database\Bootstrap;

use Cosmic\Core\Bootstrap\Model;
use Cosmic\Database\Driver\Driver;
use Cosmic\Database\Common\Query;
use Cosmic\Database\Common\QueryResult;
use Cosmic\Database\Common\ConnectionString;

/**
 * This class represents and abstract model-based database. 
 */
abstract class Database
{
    /**
     * @var Query[] $queries A collection of query objects.
     */
    private array $queries;

    /**
     * @var Driver $driver An abstract driver class used to manage the database specific queries.
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
        $this->queries = [];
        $this->driver = $driver;
        $this->driver->setConnectionString($connectionString);
    }

    /**
     * Find the entity in the database with a particular model. 
     * Will ensure that only entity is matched, if not, this will return null.
     * 
     * @param string $modelClass The model to look for.
     * @param array $where Specfic "where string" for the query.
     * 
     * @return Model|null A model instance, or null if not found.
     */
    public abstract function find(string $modelClass, array $where = []);

    /**
     * Find all the entities in the database with a particular model. 
     * 
     * @param string $modelClass The model to look for.
     * @param array $where Specfic "where string" for the query.
     * @param string $append Can be any string.
     * 
     * @return array|null A collection of models.
     */
    public abstract function findAll(string $modelClass, array $where = [], string $append = "");

    /**
     * Save the current entity model to the database. 
     * 
     * @param Model $model The model to store into the database.
     * 
     * @return void
     */
    public abstract function save(Model $model): void;

    /**
     * Delete the entity model from the database. If the model is not found, nothing will happen.
     * 
     * @param Model $model The model to search and delete from the database.
     * 
     * @return void
     */
    public abstract function delete(Model $model): void;

    /**
     * Delete the entity model from the database. If the model is not found, nothing will happen.
     * 
     * @param Model[] $models The collection (list) of models to store in the database.
     * 
     * @return void
     */
    public abstract function saveAll(array $models): void;

    /**
     * Delete all the models from the database. 
     * 
     * @param Model[] $models The collection (list) of models to delete from the database.
     * 
     * @return void
     */
    public abstract function deleteAll(array $models): void;

    /**
     * Append a new query to the commit queue.
     * 
     * @param string $query A query statement.
     * @param array $queryData Context for this new query.
     * 
     * @return void
     */
    public function addQuery(string $query, array $queryData = []): void
    {
        $this->queries[] = new Query(trim($query), $queryData);
    }

    /**
     * Execute the stored query queue and return all results.
     * 
     * @return QueryResult The result data-set from the database.
     * @throws QueryExecutionException If something goes wrong during execution.
     */
    public function commit(): QueryResult
    {
        $results = [];
        $this->driver->openConnection();

        foreach ($this->queries as $query) {
            $temp = $this->driver->executeBatch($query->getStatement(), $query->getData());
            $results[] = $temp;
        }

        $this->queries = [];
        $this->driver->closeConnection();

        $results = (count($results) == 1) ? $results[0] : $results;

        return new QueryResult($results);
    }
}
