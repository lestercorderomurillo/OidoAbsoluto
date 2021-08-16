<?php

namespace VIP\HTTP\Server\Response;

use VIP\Controller\BaseController;
use VIP\HTTP\Server\Response\AbstractResponse;

class Redirect extends AbstractResponse
{
    private string $new_path;

    public function __construct(string $new_path)
    {
        $this->new_path = $new_path;
    }

    protected function handleOperation()
    {
        $fq = BaseController::getFQCurrentControllerName();
        $this->logger->debug("{0} route redirection to '{1}'", [$fq, $this->new_path]);
        $this->setHeader("Location", __URL__ . $this->new_path);
    }
}
