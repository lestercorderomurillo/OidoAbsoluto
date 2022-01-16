<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\ResponseGenerators;

use Cosmic\HTTP\Server\Response;
use Cosmic\Core\Interfaces\ResponseGenerator;

/**
 * A response that performs a simple 301 HTTP redirection.
 */
class RedirectResponseGenerator implements ResponseGenerator
{
    /**
     * @var string $destinationURL The target url to redirect. 
     */
    private string $destinationURL;

    /**
     * Constructor. Sets the destination URL.
     * 
     * @param string $destinationURL The destination URL to redirect.
     * 
     * @return void
     * 
     * @throws \InvalidArgumentException If the destination URL has a slash at the beginning.
     */
    public function __construct(string $destinationURL)
    {
        if(str_starts_with($destinationURL, "/")){
            throw new \InvalidArgumentException("URL must not start with a slash.");
        }
        
        $this->destinationURL = __HOST__ . $destinationURL;
    }

    /**
     * Generates a valid HTTP response that will perform a redirection.
     * 
     * @return Response The generated response.
     */
    public function toResponse(): Response
    {
        $response = new Response();

        $response->setStatusCode(301);
        $response->addHeader("Location", $this->destinationURL);

        return $response;
    }
}
