<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Security\Middlewares;

use Cosmic\HTTP\Request;
use Cosmic\Security\Authorization;
use Cosmic\Core\Abstracts\Middleware;

/**
 * When used, this middleware will ensure that only administrators users are allowed to access the page.
 */
class AdministratorMiddleware extends Middleware
{
    /**
     * @inheritdoc
     */
    public function handle(Request $request): Request
    {
        if (Authorization::getCurrentRole() !== Authorization::ADMIN) {
            $this->error("Acceso denegado (solo administradores).");
            return $this->redirect("index");
        }

        return $request;
    }
}
