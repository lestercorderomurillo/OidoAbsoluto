<?php

namespace Cosmic\Bundle\Middlewares;

use Cosmic\Binder\Authorization;
use Cosmic\HTTP\Request;
use Cosmic\Core\Bootstrap\Middleware;

/**
 * When used, this middleware will ensure that only administrators users are allowed to access the page.
 */
class AdministratorMiddleware  extends Middleware
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
