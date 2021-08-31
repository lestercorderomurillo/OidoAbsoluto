<?php

namespace Pipeline\Middleware;

use Pipeline\HTTP\Message;
use Pipeline\Middleware\Middleware;
use function Pipeline\Accessors\Session;

class Authorization extends Middleware
{
    public function handle($request): Message
    {
        if (!Session()->has("logged")) {

            Session("message-type", "danger");
            Session("message", "Acceso denegado / Access Denied");
            
            return $this->redirect("index");
        }

        /* Forward request */
        return $request;
    }
}
