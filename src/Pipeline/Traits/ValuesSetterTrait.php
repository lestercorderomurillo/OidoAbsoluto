<?php

namespace Pipeline\Traits;

trait ValuesSetterTrait
{
    public function setValues(array $values, $override = true)
    {
        foreach ($values as $key => $val) {
            if ($override) {
                if (property_exists(get_class($this), $key))
                    $this->$key = $val;
            } else {
                if (property_exists(get_class($this), $key) && !isset($this->key))
                    $this->$key = $val;
            }
        }
    }
}
