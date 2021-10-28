<?php

namespace Pipeline\Buildin\Middleware;

use Pipeline\Core\Middleware;
use Pipeline\HTTP\Message;

use function Pipeline\Navigate\session;

class Authentication extends Middleware
{
    public function handle($request): Message
    {
        if (!session()->has("logged")) {

            session("error", "Acceso denegado / Access Denied");
            session("error-type", "danger");
            
            return $this->redirect("index");
        }

        /* Forward request */
        return $request;
    }
}
