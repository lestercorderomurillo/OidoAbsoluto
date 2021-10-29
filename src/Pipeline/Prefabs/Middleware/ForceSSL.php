<?php

namespace Pipeline\Prefabs\Middleware;

use Pipeline\Core\Boot\MiddlewareBase;
use Pipeline\HTTP\Message;

class ForceSSL extends MiddlewareBase
{
    public function handle($request): Message
    {
        if ($request->getProtocol() != "https") {
            return $this->redirect($request->getPath());
        }

        return $request;
    }
}
