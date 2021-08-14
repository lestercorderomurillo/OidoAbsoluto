<?php

namespace VIP\HTTP\Server\Response;

use VIP\HTTP\Server\Response\AbstractResponse;

class Response extends AbstractResponse
{
    private string $value;

    public function __construct($value = "")
    {
        $this->value = "$value";
    }

    protected function handleOperation()
    {
        echo ($this->value);
    }
}
