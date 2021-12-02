<?php

namespace Cosmic\Prefabs\Middleware;

use Cosmic\Core\Boot\Middleware;
use Cosmic\HTTP\Message;
use function Cosmic\Kernel\session;

class Authentication extends Middleware
{
    public function handle($request): Message
    {
        if (!session()->has("logged")) {
            $this->alert("Acceso denegado (sin autorización).", "danger");
            return $this->redirect("index");
        }

        return $request;
    }
}
