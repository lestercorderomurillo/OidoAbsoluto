<?php

namespace VIP\Core;

use Psr\Log\LoggerAwareTrait;

require_once("Accessor.php");

abstract class BaseObject
{
    use LoggerAwareTrait;

    private string $id;

    public function setObjectID(string $id)
    {
        $this->id = $id;
        return $this;
    }

    public function setObjectValues(array $values, $override = true)
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

    public function getObjectID()
    {
        return $this->id;
    }

    public function getClassName()
    {
        return static::class;
    }

    public function tryGet(&$var, $default = NULL)
    {
        return (isset($var) ? $var : $default);
    }
}
