<?php

namespace Pipeline\Traits;

use Pipeline\Core\Container\ContainerInterface;

trait ValuesSetterTrait
{
    public function setValues($values_or_container, $override = true)
    {
        $values = $values_or_container;
        if($values_or_container instanceof ContainerInterface){
            $values = $values->expose();
        }
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
