<?php

namespace Cosmic\Traits;

/**
 * This trait allows classes to create a string representation when being printed.
 */
trait StringableTrait
{
    /**
     * Convert this object to a string representation.
     * 
     * @return string
     */
    public abstract function toString(): string;

    /**
     * Magic method to be executed when PHP need to parse a object into a string.
     * This method will only call the non-magic method. Should not be overridden.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
