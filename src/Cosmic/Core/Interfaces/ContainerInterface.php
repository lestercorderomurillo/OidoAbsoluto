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
interface ContainerInterface extends ReadOnlyContainerInterface
{
    /**
     * Clears all elements from the container.
     * @return void
     */
    public function clear(): void;

    /**
     * Add a new value to the container. Return true if the value was successfully added.
     * Containers can implement the internal handling of their elements. (Insert, Stack, etc)
     * 
     * @param mixed $key To bind the given value.
     * @param mixed $value Can be anything.
     * @return bool True if the value was successfully added, false otherwise.
     * @throws ForbiddenWriteException Must throw this exception When trying to write on read-only containers.
     */
    public function add($key, $value): bool;

    /**
     * Add a new value to the container. Return true if the value was successfully deleted.
     * 
     * @param mixed $key To bind the try to delete from the container.
     * @return bool True if the value was successfully deleted, false otherwise.
     * @throws ForbiddenWriteException Must throw this exception When trying to write on read-only containers.
     */
    public function delete($key): bool;
}
