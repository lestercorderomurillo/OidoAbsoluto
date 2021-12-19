<?php

namespace Cosmic\Core\Interfaces;

/**
 * This class represents containers with read access and operations.
 */
interface ReadOnlyContainerInterface
{
    /**
     * Return if the container has the given key.
     * 
     * @param string $key The key to check for existence.
     * 
     * @return bool True if the key exists.
     */
    public function has(string $key): bool;

    /**
     * Return the value stored with the given key.
     * 
     * @param string $key The key to retrieve from the container.
     * 
     * @return mixed The entry value.
     */
    public function get(string $key);
}
