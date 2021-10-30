<?php

namespace Pipeline\Core\Boot;

use function Pipeline\Kernel\safe;

abstract class NodeBase
{
    private string $id;
    private array $data = [];

    public function addChild(string $key, $child): NodeBase
    {
        $this->data[$key] = $child;
        return $this;
    }

    public function getChild(string $key, bool $safe = false): NodeBase
    {
        if ($safe) {
            return safe($this->data[$key]);  
        }
        return $this->data[$key];
    }

    public function deleteChild(string $key): NodeBase
    {
        unset($this->data[$key]);
        return $this;
    }

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
