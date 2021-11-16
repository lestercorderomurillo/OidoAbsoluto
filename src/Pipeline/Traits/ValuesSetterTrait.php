<?php

namespace Pipeline\Traits;

trait ValuesSetterTrait
{
    public function setValues(array $values, $override = true): void
    {
        foreach ($values as $key => $value) {
            if ($override) {
                if (property_exists(get_class($this), $key))
                    $this->$key = $value;
            } else {
                if (property_exists(get_class($this), $key) && !isset($this->key))
                    $this->$key = $value;
            }
        }
    }
}
