<?php

namespace Pipeline\Core;

abstract class Node
{
    private string $id;

    public function setId(string $id): Node
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
