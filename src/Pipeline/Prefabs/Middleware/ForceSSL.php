<?php

namespace Cosmic\Prefabs\Middleware;

use Cosmic\Core\Boot\Middleware;
use Cosmic\HTTP\Message;

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
