<?php

namespace Pipeline\HTTP\Server;

use Pipeline\Core\Loader;
use Pipeline\Traits\DefaultAccessorTrait;

class Session extends Loader
{
    use DefaultAccessorTrait;

    protected static function __load(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function __construct()
    {
        Session::load();
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
