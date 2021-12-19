<?php

namespace Cosmic\Core\Interfaces;

/**
 * This class represents containers with all expected functionality.
 */
interface ContainerInterface extends WriteableContainerInterface
{
    /**
     * Clears all elements from the container.
     * 
     * @return void
     */
    public function clear(): void;

    /**
     * Return all elements from the container.
     * 
     * @return array
     */
    public function all(): array;
}
