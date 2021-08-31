<?php

namespace Pipeline\Traits;

trait StringableTrait
{
    public abstract function toString(): ?string;

    public function __toString()
    {
        return $this->toString();
    }
}
