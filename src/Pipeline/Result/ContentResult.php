<?php

namespace Pipeline\Result;

use Pipeline\Core\ResultInterface;
use Pipeline\HTTP\Server\ServerResponse;

class ContentResult implements ResultInterface
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function toResponse(): ServerResponse
    {
        $response = new ServerResponse();
        $response->addHeader("Content-Type", "text/html");
        $response->setBody($this->value);
        return $response;
    }
}
