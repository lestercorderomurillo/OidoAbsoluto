<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Traits;

/**
 * This trait allows classes to create a string representation when being printed/echoed.
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
