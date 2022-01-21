<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\ResponseGenerators;

use Cosmic\Core\Interfaces\ResponseGenerator;
use Cosmic\HTTP\Server\Response;

/**
 * A simple text response generator.
 */
class ContentResponseGenerator implements ResponseGenerator
{
    /**
     * @var string $value The underlying content value.
     */
    private string $value;

    /**
     * Constructor. Sets the value from the input.
     * 
     * @param string $value A simple string.
     * 
     * @return void
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Generates a valid HTTP response containg a string based body.
     * 
     * @return Response The generated response.
     */
    public function toResponse(): Response
    {
        $response = new Response();
        $response->addHeader("content-type", "text/html");
        $response->setBody($this->value);

        return $response;
    }
}
