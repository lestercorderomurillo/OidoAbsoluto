<?php

namespace Cosmic\Database\Common;

use function Cosmic\Core\Boot\configuration;

/**
 * This call will automatically fill the connection string using default keys.
 */
class MySQLConnectionString extends ConnectionString
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
            configuration("database.mysql.server"),
            configuration("database.mysql.db"),
            configuration("database.mysql.user"),
            configuration("database.mysql.pass")
        );
    }
}
