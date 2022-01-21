<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\ORM\Common;

use Cosmic\Traits\StringableTrait;
use Cosmic\Core\Interfaces\FactoryInterface;

/**
 * A simple abstraction for database connection strings.
 */
class ConnectionString implements FactoryInterface
{
    use StringableTrait;

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
     * @var string $dataSource The stored database name.
     */
    private string $dataSource;

    /**
     * Constructor.
     * 
     * @param string $host The source of the host.
     * @param string $dataSource The database to select.
     * @param string $username The username to use to authentificate. Empty by default.
     * @param string $password The password for this user. Empty by default.
     * 
     * @return string
     */
    public function __construct(string $host, string $dataSource, string $username = __EMPTY__, string $password = __EMPTY__)
    {
        $this->host = $host;
        $this->dataSource = $dataSource;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @inheritdoc
     */
    public static function from($dataSource)
    {
        return json_decode($dataSource);
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
     * Return the dataSource name.
     * 
     * @return string The dataSource name.
     */
    public function getDataSourceName()
    {
        return $this->dataSource;
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

    /**
     * @inheritdoc
     */
    public function toString(): string
    {
        return json_encode($this);
    }
}
