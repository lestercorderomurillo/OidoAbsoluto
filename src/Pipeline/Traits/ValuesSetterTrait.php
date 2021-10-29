<?php

namespace Pipeline\Traits;

use Pipeline\Core\Facade\ContainerInterface;

trait ValuesSetterTrait
{
    public function setValuesByContainer(ContainerInterface $container, $override = true): void
    {
        return $this->setValues($container->exposeArray(), $override);
    }

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
