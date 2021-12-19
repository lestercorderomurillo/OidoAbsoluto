<?php

namespace Cosmic\Prefabs\Middleware;

use Cosmic\HTTP\Request;
use Cosmic\Core\Boot\Middleware;
use Cosmic\HTTP\Server\Response;

/**
 * This middleware will ensure that if the HTTPS protocol is enabled, all incoming users requests will be
 * automatically redirected if they don't comply with established configuration policy.
 */
class ForceSSL extends Middleware
{
    public function handle(Request $request): Request
    {
        if ($request->getProtocol() != "https") {

            $response = Response::create($request);
            $response->setProtocol("https");

            return $this->redirect($response->getFullPath());
        }

        return $request;
    }
}
