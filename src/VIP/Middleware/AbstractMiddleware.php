<?php

namespace VIP\Middleware;

use Psr\Log\LoggerAwareTrait;
use VIP\Core\BaseObject;
use VIP\HTTP\Common\Request;

use function VIP\Core\Logger;

abstract class AbstractMiddleware extends BaseObject
{
    use LoggerAwareTrait;

    public function stopRequestForwarding()
    {
        $this->setLogger(Logger());
        $this->logger->debug("{1} catch the request. Forwarding in pipeline stopped.", ["1" => $this->getClassName()]);
    }

    public abstract function handle(Request $request): Request;
}
