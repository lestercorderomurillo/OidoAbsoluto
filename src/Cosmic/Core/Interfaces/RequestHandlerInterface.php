<?php

namespace Cosmic\Core\Interfaces;

use Cosmic\HTTP\Request;

/**
 * This class represents objects that can handle requests.
 */
interface RequestHandlerInterface
{
    /**
     * Handle the request and return another one in exchange.
     * Response objects are considered to be request objects,
     * but with write access this properties.
     * 
     * @param Request $request An HTTP request instance.
     * 
     * @return Request|null A request object or null to stop the chain of handlers.
     */
    public function handle(Request $request): Request;
}