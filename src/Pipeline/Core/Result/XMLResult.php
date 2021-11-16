<?php

namespace Pipeline\Core\Result;

use Pipeline\Core\Types\XML;
use Pipeline\Core\Interfaces\ResultInterface;
use Pipeline\HTTP\Server\ServerResponse;

class XMLResult implements ResultInterface
{
    private XML $xml;

    public function __construct(XML $xml)
    {
        $this->xml = $xml;
    }

    public function toResponse(): ServerResponse
    {
        $response = new ServerResponse();
        $response->addHeader("Content-Type", "application/xml");
        $response->setBody($this->xml);
        return $response;
    }
}
