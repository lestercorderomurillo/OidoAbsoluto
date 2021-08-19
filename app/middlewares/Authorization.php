<?php

namespace App\Middlewares;

use Pipeline\HTTP\Common\Request;
use Pipeline\Middleware\Middleware;
use Pipeline\Result\RedirectResult;
use function Pipeline\Accessors\Session;

class Authorization extends Middleware
{
    public function handle(Request $request): Request
    {
        if (!Session()->has("logged")) {

            Session("message-type", "danger");
            Session("message", "Debes haber iniciado sesión para acceder a esta vista.");

            $this->stopRequestForwarding();
            
            $response = new RedirectResult("login");
            $response->handle();
        }

        return $request;
    }
}
