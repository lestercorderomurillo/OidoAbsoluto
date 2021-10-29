<?php

namespace Pipeline\Prefabs\Middleware;

use Pipeline\Core\Middleware;
use Pipeline\HTTP\Message;

class ForceSSL extends Middleware
{
    public function handle($request): Message
    {
        if ($request->getProtocol() != "https") {
            return $this->redirect($request->getPath());
        }

        return $request;
    }
}
