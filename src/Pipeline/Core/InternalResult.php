<?php

namespace Pipeline\Core;

use Pipeline\Traits\DefaultAccessorTrait;

class InternalResult
{
    use DefaultAccessorTrait;

    // remove, and change this for pipelineStage and exceptions
    public const SUCCESS = 0;
    public const FAILURE = -1;

    private array $data;
    private int $status;

    public function __construct(array $data = [], int $status = InternalResult::SUCCESS)
    {
        $this->data = $data;
        $this->status = $status;
    }

    public function getData(string $id)
    {
        return $this->tryGet($this->data["$id"], NULL);
    }

    public function getAllData()
    {
        return $this->data;
    }

    public function getStatus()
    {
        return $this->status;
    }
}
