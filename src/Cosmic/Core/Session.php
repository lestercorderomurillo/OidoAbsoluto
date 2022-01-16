<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core;

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
        $this->build();
    }

    /**
     * Create a new PHP session.
     */
    private function build()
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
    public function get($key)
    {
        return tryGet($_SESSION[$key], "");
    }

    /**
     * @inheritdoc
     */
    public function add($key, $value): bool
    {
        $_SESSION[$key] = $value;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete($key): bool
    {
        unset($_SESSION[$key]);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function has($key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
        session_destroy();
        $this->build();
    }
}
