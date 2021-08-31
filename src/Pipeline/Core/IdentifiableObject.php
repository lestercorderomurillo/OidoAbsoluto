<?php

namespace Pipeline\Core;

abstract class IdentifiableObject
{
    private string $id;

    public function setId(string $id): IdentifiableObject
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
