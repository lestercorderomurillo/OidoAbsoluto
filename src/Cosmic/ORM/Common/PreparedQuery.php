<?php

namespace Cosmic\ORM\Common;

use Cosmic\Traits\StringableTrait;

/**
 * This class represents a simple database query that can be executed using a database.
 */
class PreparedQuery
{
    use StringableTrait;

    /**
     * @var string $statement The statement to execute.
     */
    private string $statement;

    /**
     * @var mixed $data The parameters used in this query context.
     */
    private array $data;

    /**
     * Constructor. Builds a new query.
     * 
     * @param string statement: The statement for this query.
     * @param array data: The data to bind.
     */
    public function __construct(string $statement, array $data = [])
    {
        $this->statement = trim($statement);
        $this->data = $data;
    }

    /**
     * Return the current data.
     * 
     * @param string statement: The statement for this query.
     * 
     * @return array data: The data to bind.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Return the statement for this query.
     * 
     * @return string
     */
    public function getStatement(): string
    {
        return $this->statement;
    }

    /**
     * Return the statement for this query.
     * 
     * @return string
     */
    public function toString()
    {
        $string = $this->statement;

        foreach ($this->data as $key => $value) {
            //$string = str_replace($key, htmlentities($value), $string);
            $string = str_replace($key, $value, $string);
        }

        return $string;
    }
}
