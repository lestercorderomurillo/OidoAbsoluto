<?php

namespace Cosmic\Core\Interfaces;

/**
 * This class represents containers with write access and operations.
 */
interface WriteableContainerInterface extends ReadOnlyContainerInterface
{
    /**
     * Add a new value to the container. Return true if the value was successfully added.
     * Containers can implement the internal handling of their elements. (Insert, Stack, etc)
     * 
     * @param string $key To bind the given value.
     * @param mixed $value Can be anything.
     * 
     * @return bool True if the value was successfully added, false otherwise.
     */
    public function add(string $key, $value): bool;

    /**
     * Add a new value to the container. Return true if the value was successfully deleted.
     * 
     * @param string $key To bind the try to delete from the container.
     * 
     * @return bool True if the value was successfully deleted, false otherwise.
     */
    public function delete(string $key): bool;
}
