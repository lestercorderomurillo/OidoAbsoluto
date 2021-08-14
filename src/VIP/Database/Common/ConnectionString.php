<?php

namespace VIP\Database\Common;

class ConnectionString
{
    private string $host;
    private string $user;
    private string $pass;
    private string $db_name;

    public function __construct(string $host, string $db_name, string $user = "", string $pass = "")
    {
        $this->host = $host;
        $this->db_name = $db_name;
        $this->user = $user;
        $this->pass = $pass;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getDatabaseName()
    {
        return $this->db_name;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getPassword()
    {
        return $this->pass;
    }
}
