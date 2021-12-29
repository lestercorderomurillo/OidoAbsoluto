<?php

namespace Cosmic\Database\Common;

use function Cosmic\Core\Bootstrap\safe;

/**
 * This class represents a simple database result data-set retrieved after performing a query.
 */
class QueryResult
{
    /**
     * @var mixed $data The result dataset from the query result.
     */
    private $data;

    /**
     * Constructor.
     * 
     * @param array|string data: To be stored.
     * 
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Return true if the attribute is present in the query result.
     * 
     * @param string id: The attribute to check if it exists.
     * 
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->data["$id"]);
    }

    /**
     * Return the specified attribute from the query result set.
     * 
     * @param string id: The attribute to try to get in simple index.
     * 
     * @return mixed
     */
    public function get(string $id)
    {
        return safe($this->data["$id"], null);
    }

    /**
     * Return all the data returned by this query.
     * 
     * @return mixed
     */
    public function all()
    {
        return $this->data;
    }
}
