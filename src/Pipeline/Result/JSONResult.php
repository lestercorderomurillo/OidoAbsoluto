<?php

namespace Pipeline\Result;

use Pipeline\Core\Types\JSON;
use Pipeline\Core\Facade\ResultInterface;
use Pipeline\HTTP\Server\ServerResponse;

class JSONResult implements ResultInterface
{
    private JSON $json;

    public function __construct(JSON $json)
    {
        $this->json = $json;
    }

    public function toResponse(): ServerResponse
    {
        $response = new ServerResponse();
        $response->addHeader("Content-Type", "application/json");
        $response->setBody($this->json);
        return $response;
    }
}
