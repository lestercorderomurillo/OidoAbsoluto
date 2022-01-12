<?php

namespace Cosmic\Bundle\Middlewares;

use Cosmic\Binder\Authorization;
use Cosmic\HTTP\Request;
use Cosmic\Core\Bootstrap\Middleware;

/**
 * When used, this middleware will ensure that only regular users are allowed to access the page.
 */
class RegularUserMiddleware extends Middleware
{
    /**
     * @inheritdoc
     */
    public function handle(Request $request): Request
    {
        if (Authorization::getCurrentRole() !== Authorization::USER) {
            $this->error("Acceso denegado (solo usuarios regulares).");
            return $this->redirect("index");
        }

        return $request;
    }
}
