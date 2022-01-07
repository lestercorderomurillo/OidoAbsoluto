<?php

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
     * @param bool $override = true When true, will override all values when posible.
     * 
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
