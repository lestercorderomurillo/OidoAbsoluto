<?php

namespace Cosmic\ORM\Bootstrap;

use Cosmic\ORM\Common\PreparedQuery;
use Cosmic\ORM\Common\ConnectionString;
use Cosmic\ORM\Common\QueryResult;

/**
 * This class represents an abstract model-based database. 
 */
abstract class Database
{
    /**
     * @var Query[] $queryQueue A collection of query objects that can be compiled into a single query string.
     */
    private array $queryQueue;

    /**
     * @var Driver $driver An abstract driver class used to manage the database specific abstrController.
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
        $this->queryQueue = [];
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
     * Check if the given model exists in the database.
     * 
     * @param string $modelClass The model to look for.
     * @param array $where Specfic "where string" for the query.
     * 
     * @return bool True if it exists, false otherwise.
     */
    public abstract function exists(string $modelClass, array $where);

    /**
     * Find all the entities in the database with a particular model. 
     * 
     * @param string $modelClass The model to look for.
     * @param array $where Specfic "where string" for the query.
     * @param string $append Can be any string.
     * 
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
    public abstract function save($models): void;

    /**
     * Delete the entity model from the database. If the model is not found, nothing will happen.
     * 
     * @param Models[]|Model The model to search and delete from the database.
     * 
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
        $this->driver->openConnection();

        foreach ($this->queryQueue as $query) {
            $results[] = $this->driver->execute($query->getStatement(), $query->getData());
        }

        $this->driver->closeConnection();
        $this->queryQueue = [];

        return $results;
    }

    /**
     * Perform a custom query and return the results.
     * 
     * @param string $query A query statement.
     * @param array $data Context for the query.
     * 
     * @return QueryResult — The result data-set from the database.
     */
    protected function query(string $query, array $queryData = []): QueryResult
    {
        $this->addQueryToNextBatch($query, $queryData);
        return $this->commit()[0];
    }

    /**
     * Append a new query to the commit queue.
     * 
     * @param string $query A query statement.
     * @param array $data Context for the query.
     * 
     * @return void
     */
    protected function addQueryToNextBatch(string $query, array $data = []): void
    {
        $this->queryQueue[] = new PreparedQuery(trim($query), $data);
    }
}
