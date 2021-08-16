<?php

namespace App\Middlewares;

use VIP\HTTP\Common\Request;
use VIP\HTTP\Server\Response\Redirect;
use VIP\Middleware\AbstractMiddleware;

use function VIP\Core\Session;

class Authorization extends AbstractMiddleware
{
    public function handle(Request $request) : Request
    {
        if(!Session()->has("logged")){
            Session()->store("message-type", "danger");
            Session()->store("message", "Debes haber iniciado sesión para acceder a esta vista.");
            $this->stopRequestForwarding();
            $response = new Redirect("login");
            $response->handle();
        }
        
        return $request;
    }
}
