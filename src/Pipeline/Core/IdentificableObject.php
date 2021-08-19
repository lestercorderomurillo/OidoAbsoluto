<?php

namespace Pipeline\Core;

abstract class IdentificableObject
{
    private string $id;

    public function setId(string $id): IdentificableObject
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
