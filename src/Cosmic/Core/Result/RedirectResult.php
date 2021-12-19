<?php

namespace Cosmic\Core\Result;

use Cosmic\Core\Interfaces\ResultGeneratorInterface;
use Cosmic\HTTP\Server\Response;

/**
 * A response that performs a simple 301 HTTP redirection.
 */
class RedirectResult implements ResultGeneratorInterface
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
     */
    public function __construct(string $destinationURL)
    {
        $this->destinationURL = $destinationURL;
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
