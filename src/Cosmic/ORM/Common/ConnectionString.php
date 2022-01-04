<?php

namespace Cosmic\ORM\Common;

/**
 * A simple abstraction for database connection strings.
 */
class ConnectionString
{
    /**
     * @var string $host The host used to connect to the database.
     */
    private string $host;

    /**
     * @var string $username The username used to authentificate.
     */
    private string $username;

    /**
     * @var string $password The stored password.
     */
    private string $password;

    /**
     * @var string $dbName The stored database name.
     */
    private string $dbName;

    /**
     * Constructor.
     * 
     * @param string $host The source of the host.
     * @param string $dbName The database to select.
     * @param string $username The username to use to authentificate. Empty by default.
     * @param string $password The password for this user. Empty by default.
     * 
     * @return string
     */
    public function __construct(string $host, string $dbName, string $username = "", string $password = "")
    {
        $this->host = $host;
        $this->dbName = $dbName;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Return the host.
     * 
     * @return string The host.
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Return the database name.
     * 
     * @return string The database name.
     */
    public function getDatabaseName()
    {
        return $this->dbName;
    }

    /**
     * Return the username.
     * 
     * @return string The username.
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Return the password.
     * 
     * @return string The password.
     */
    public function getPassword()
    {
        return $this->password;
    }
}
