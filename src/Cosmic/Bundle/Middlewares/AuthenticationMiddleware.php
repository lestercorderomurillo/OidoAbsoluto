<?php

namespace Cosmic\Bundle\Middlewares;

use Cosmic\Binder\Authorization;
use Cosmic\HTTP\Request;
use Cosmic\Core\Bootstrap\Middleware;

/**
 * When used, this middleware will ensure that if the user is not logged, put a message and redirect to another page.
 */
class AuthenticationMiddleware extends Middleware
{
    /**
     * @inheritdoc
     */
    public function handle(Request $request): Request
    {
        if (!Authorization::isLogged()) {
            $this->error("Acceso denegado (requiere iniciar sesión).");
            return $this->redirect("index");
        }

        return $request;
    }
}
