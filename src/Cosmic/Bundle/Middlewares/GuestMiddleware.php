<?php

namespace Cosmic\Bundle\Middlewares;

use Cosmic\Binder\Authorization;
use Cosmic\HTTP\Request;
use Cosmic\Core\Bootstrap\Middleware;

/**
 * When used, this middleware will ensure that only GuestMiddlewares users are allowed to access the page.
 */
class GuestMiddleware extends Middleware
{
    /**
     * @inheritdoc
     */
    public function handle(Request $request): Request
    {
        if (Authorization::isLogged()) {
            $this->error("Acceso denegado (solamente visitantes).");
            return $this->redirect("index");
        }

        return $request;
    }
}
