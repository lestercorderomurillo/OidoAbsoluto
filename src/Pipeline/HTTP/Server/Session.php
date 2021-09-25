<?php

namespace Pipeline\HTTP\Server;

use Pipeline\Core\StaticObjectInitializer;
use Pipeline\Traits\DefaultAccessorTrait;

class Session extends StaticObjectInitializer
{
    use DefaultAccessorTrait;

    protected static function __initialize(): void
    {
        session_start();
        session_regenerate_id();
    }

    public function __construct()
    {
        $this->initializeOnce();
    }

    public function expose(): array
    {
        return $_SESSION;
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
