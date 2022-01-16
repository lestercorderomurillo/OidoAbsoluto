<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Interfaces;

use Cosmic\HTTP\Request;

/**
 * This class represents objects that have the capability to handle HTTP requests.
 */
interface RequestHandlerInterface
{
    /**
     * Handle the request and return another one in exchange.
     * Response objects are considered to be request objects, but with write-access this properties.
     * 
     * @param Request $request An HTTP request instance.
     * @return Request|null A request object. When null is returned, the chain of handlers 
     * from the top parent object will stop executing further.
     */
    public function handle(Request $request): Request;
}