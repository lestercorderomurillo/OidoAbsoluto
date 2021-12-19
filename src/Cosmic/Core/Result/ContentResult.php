<?php

namespace Cosmic\Core\Result;

use Cosmic\Core\Interfaces\ResultGeneratorInterface;
use Cosmic\HTTP\Server\Response;

/**
 * A simple text response generator.
 */
class ContentResult implements ResultGeneratorInterface
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
