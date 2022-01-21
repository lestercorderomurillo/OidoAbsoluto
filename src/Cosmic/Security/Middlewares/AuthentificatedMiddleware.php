<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Security\Middlewares;

use Cosmic\HTTP\Request;
use Cosmic\Security\Authorization;
use Cosmic\Core\Abstracts\Middleware;

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
