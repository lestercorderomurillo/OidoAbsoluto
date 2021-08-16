<?php

namespace VIP\HTTP\Server\Response;

use VIP\Controller\BaseController;
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
        $this->logger->debug(
            "{0} responded with '{1}' : '{2}'",
            [
                BaseController::getFQCurrentControllerName(),
                $this->getStatusCode(),
                $this->value
            ]
        );
        echo ($this->value);
    }
}
