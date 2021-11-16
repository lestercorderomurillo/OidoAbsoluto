<?php

namespace Pipeline\Core\Result;

use Pipeline\Core\Interfaces\ResultInterface;
use Pipeline\HTTP\Server\ServerResponse;

class RedirectResult implements ResultInterface
{
    private string $new_path;

    public function __construct(string $new_path)
    {
        $this->new_path = $new_path;
    }

    public function toResponse(): ServerResponse
    {
        $response = new ServerResponse();
        $response->addHeader("Location",  __URL__ . $this->new_path);
        return $response;
    }
}
