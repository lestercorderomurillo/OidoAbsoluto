<?php

namespace Pipeline\HTTP\Server;

use function Pipeline\Kernel\safe;

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function exposeArray(): array
    {
        return $_SESSION;
    }

    public function get(string $key)
    {
        return safe($_SESSION[$key], "");
    }

    public function store(string $key, string $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function clear(): void
    {
        session_destroy();
    }

    public function discard(array $keys): void
    {
        foreach ($this->exposeArray() as $key => $value) {
            if (array_key_exists($key, $keys) || $this->has($key)) {
                $this->remove($key);
            }
        }
    }
}
