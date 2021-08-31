<?php

namespace Pipeline\HTTP\Server;

use Pipeline\Core\StaticObjectInterface;
use Pipeline\Traits\DefaultAccessorTrait;

class Session implements StaticObjectInterface
{
    use DefaultAccessorTrait;

    public static function __initialize(): void
    {
        session_start();
        session_regenerate_id();
    }

    public function get(string $key)
    {
        return $this->tryGet($_SESSION[$key], "");
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
