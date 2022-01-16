<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Interfaces;

/**
 * This class represents containers with all expected functionality.
 */
interface ReadOnlyContainerInterface
{
    /**
     * Return if the container has the given key.
     * 
     * @param mixed $key The key to check for existence.
     * @return bool True if the key exists.
     */
    public function has($key): bool;

    /**
     * Return the value stored with the given key.
     * 
     * @param mixed $key The key to retrieve from the container.
     * @return mixed The entry value.
     */
    public function get($key);

    /**
     * Return all elements from the container.
     * @return array All elements.
     */
    public function all(): array;
}
