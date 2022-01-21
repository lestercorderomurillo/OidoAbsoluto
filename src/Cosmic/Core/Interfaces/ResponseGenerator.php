<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Interfaces;

use Cosmic\HTTP\Server\Response;

/**
 * This class represents objects that can be converted to HTTP responses.
 */
interface ResponseGenerator
{
    /**
     * Create a new response object from this response generator.
     * 
     * @return Response The response representation of this instance.
     */
    public function toResponse(): Response;
}
