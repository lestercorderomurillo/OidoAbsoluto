<?php

namespace Cosmic\HTTP\Server;

use Cosmic\Core\Interfaces\ContainerInterface;

/**
 * This class represents a session. For the time being, this class uses the PHP standard session storage mechanism.
 * In future releases, drivers can be configured to store sessions in different ways.
 */
class Session implements ContainerInterface
{
    /**
     * Constructor. Will build a new PHP session if necessary.
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * @inheritdoc
     */
    public function all(): array
    {
        return $_SESSION;
    }

    /**
     * @inheritdoc
     */
    public function get(string $key)
    {
        return safe($_SESSION[$key], "");
    }

    /**
     * @inheritdoc
     */
    public function add(string $key, $value): bool
    {
        $_SESSION[$key] = $value;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete(string $key): bool
    {
        unset($_SESSION[$key]);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
        session_destroy();
    }
}
