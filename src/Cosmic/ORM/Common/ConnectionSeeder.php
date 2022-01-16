<?php

namespace Cosmic\ORM\Common;

use Cosmic\ORM\Common\ConnectionString;
use Cosmic\Core\Interfaces\FactoryInterface;

/**
 * This call provides a way to create the database connection very easily.
 */
class ConnectionSeeder implements FactoryInterface
{
    /**
     * Creates a new connection string using the configuration file from cosmic.
     * Uses the "database.$key.*" entries.
     * 
     * @param string $key The key to use.
     * @return ConnectionString The new connection-string object.
     */
    public static function from($key)
    {
        return new ConnectionString(
            configuration("datasource.$key.server"),
            configuration("datasource.$key.db"),
            configuration("datasource.$key.user"),
            configuration("datasource.$key.pass")
        );
    }
}
