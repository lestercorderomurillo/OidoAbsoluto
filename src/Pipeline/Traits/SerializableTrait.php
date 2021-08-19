<?php

namespace Pipeline\Traits;

trait SerializableTrait
{
    public abstract function toString(): ?string;

    public function __toString()
    {
        return $this->toString();
    }
}
