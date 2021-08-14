<?php

namespace VIP\HTTP\Common;

class Session
{
    public function __construct()
    {
        session_start();
        session_regenerate_id();
    }

    public function get(string $key, $default = "")
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public function store(string $key, string $value)
    {
        $_SESSION[$key] = $value;
    }

    public function remove(string $key)
    {
        unset($_SESSION[$key]);
    }

    public function has(string $key)
    {
        return isset($_SESSION[$key]);
    }

    public function clear()
    {
        session_destroy();
    }
}
