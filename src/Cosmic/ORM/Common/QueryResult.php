<?php

namespace Cosmic\ORM\Common;

use Cosmic\Core\Interfaces\ReadOnlyContainerInterface;

/**
 * This class represents a simple database query result.
 */
class QueryResult implements ReadOnlyContainerInterface
{
    /**
     * @var string $originalStatement The statement that was executed.
     */
    private string $originalStatement;

    /**
     * @var array $data The data returned from the query.
     */
    private array $databaseResponse;

    /**
     * @var int $lastestAffectedRows The number of rows changes from this query response.
     */
    private int $lastestAffectedRows;

    /**
     * @var int $lastestInsertedId The lastest inserted id inserted from this query response.
     */
    private int $lastestInsertedId;

    /**
     * Constructor. Builds a new query result-set from the database response.
     * 
     * @param string $originalStatement The original statement used for this query.
     * @param array $data The data to store for retrieval.
     * @param int $lastestUpdatedRows How many rows have been affected since the last update.
     * @param int $lastestInsertedId The lastest inserted id from this query response.
     * 
     * @return void
     */
    public function __construct(string $originalStatement, array $data = [], int $lastestUpdatedRows = 0, int $lastestInsertedId = 0)
    {
        $this->originalStatement = trim($originalStatement);
        $this->databaseResponse = $data;
        $this->lastestUpdatedRows = $lastestUpdatedRows;
        $this->lastestInsertedId = $lastestInsertedId;
    }

    /**
     * Set the data-set for this result.
     * 
     * @return void
     */
    public function setDatabaseResponse(array $data): void
    {
        $this->databaseResponse = $data;
    }

    /**
     * Set the lastest number of updated rows for this result.
     * 
     * @return void
     */
    public function setLastestUpdatedRows(int $lastestUpdatedRows): void
    {
        $this->lastestUpdatedRows = $lastestUpdatedRows;
    }

    /**
     * Set the lastest inserted id for this result.
     * 
     * @param int $lastestInsertedId The lastest inserted id.
     * 
     * @return void
     */
    public function setLastestInsertedId(int $lastestInsertedId): void
    {
        $this->lastestInsertedId = $lastestInsertedId;
    }

    /**
     * @inheritdoc
     */
    public function all(): array
    {
        return $this->databaseResponse;
    }

    /**
     * @inheritdoc
     */
    public function get(string $key)
    {
        return $this->databaseResponse[$key];
    }

    /**
     * @inheritdoc
     */
    public function has(string $key): bool
    {
        return isset($this->databaseResponse[$key]);
    }

    /**
     * Return the used statement for this query.
     * 
     * @return string
     */
    public function getOriginalStatement(): string
    {
        return $this->originalStatement;
    }

    /**
     * Return the lastest inserted ID for this query.
     * 
     * @return int
     */
    public function getInsertedId(): int
    {
        return $this->lastestInsertedId;
    }

    /**
     * Return the lastest inserted ID for this query.
     * 
     * @return int
     */
    public function getAffectedRows(): int
    {
        return $this->lastestAffectedRows;
    }
}
