<?php

namespace Pipeline\Prefabs\Middleware;

use Pipeline\Core\Boot\MiddlewareBase;
use Pipeline\HTTP\Message;
use function Pipeline\Kernel\session;

class Authentication extends MiddlewareBase
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
