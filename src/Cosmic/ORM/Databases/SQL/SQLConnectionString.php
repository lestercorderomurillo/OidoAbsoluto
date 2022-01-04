<?php

namespace Cosmic\ORM\Databases\SQL;

use Cosmic\ORM\Common\ConnectionString;

/**
 * This call will automatically fill the connection string using default keys.
 */
class SQLConnectionString extends ConnectionString
{
    /**
     * Constructor.
     * 
     * By default, loads the database connectionString from the configuration file using "database.mysql.*" keys.
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct(
            configuration("database.sql.server"),
            configuration("database.sql.db"),
            configuration("database.sql.user"),
            configuration("database.sql.pass")
        );
    }
}
