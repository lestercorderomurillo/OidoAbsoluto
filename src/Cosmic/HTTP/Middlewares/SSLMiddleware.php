<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\HTTP\Middlewares;

use Cosmic\Core\Abstracts\Middleware;
use Cosmic\HTTP\Request;
use Cosmic\HTTP\Server\Response;

/**
 * This middleware will ensure that if the HTTPS protocol is enabled, all incoming users requests will be
 * automatically redirected if they don't comply with established configuration policy.
 */
class SSLMiddleware extends Middleware
{
    /** @inheritdoc */
    public function handle(Request $request): Request
    {
        if ($request->getProtocol() != "https") {

            $response = Response::from($request);
            $response->setProtocol("https");
            return $this->redirect($response->getFullPath());
        }

        return $request;
    }
}
