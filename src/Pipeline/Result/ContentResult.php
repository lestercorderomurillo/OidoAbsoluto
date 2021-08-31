<?php

namespace Pipeline\Result;

use Pipeline\Core\ResultInterface;
use Pipeline\HTTP\Server\ServerResponse;

class ContentResult implements ResultInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = "$value";
    }

    public function toResponse(): ServerResponse
    {
        $response = new ServerResponse();
        $response->setBody($this->value);
        return $response;
    }
}
