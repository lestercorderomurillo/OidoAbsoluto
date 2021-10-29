<?php

namespace Pipeline\Core\Boot;

abstract class NodeBase
{
    private string $id;

    public function setId(string $id): NodeBase
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
