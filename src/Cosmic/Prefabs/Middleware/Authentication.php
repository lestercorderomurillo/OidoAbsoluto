<?php

namespace Cosmic\Prefabs\Middleware;

use Cosmic\Core\Boot\Middleware;
use Cosmic\HTTP\Request;
use function Cosmic\Core\Boot\session;

/**
 * When used, this middleware will ensure that if the user is not logged, put a message and redirect to another page.
 */
class Authentication extends Middleware
{
    public function handle(Request $request): Request
    {
        if (!session()->has("logged")) {

            $this->danger("Acceso denegado (sin autorización).");
            return $this->redirect("index");
            
        }

        return $request;
    }
}
