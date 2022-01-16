<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Traits;

/**
 * This trait allows classes to set their class attributes from a conventional array.
 */
trait ValuesSetterTrait
{
    /**
     * Sets properties of a class, using the given array.
     * 
     * @param array $values The collection of values to set to this object.
     * @param bool $override [Optional] When true, will override all values when posible.
     * @return void
     */
    public function setValues(array $values, bool $override = true): void
    {
        foreach ($values as $key => $value) {
            if ($override) {
                if (property_exists(get_class($this), $key))
                    $this->$key = $value;
            } else {
                if (property_exists(get_class($this), $key) && !isset($this->$key))
                    $this->$key = $value;
            }
        }
    }
}
