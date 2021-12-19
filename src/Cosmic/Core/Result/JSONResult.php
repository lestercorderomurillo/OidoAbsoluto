<?php

namespace Cosmic\Core\Result;

use Cosmic\Core\Types\JSON;
use Cosmic\Core\Interfaces\ResultGeneratorInterface;
use Cosmic\HTTP\Server\Response;

/**
 * A response that renders a JSON object as string and generates a valid HTTP response.
 */
class JSONResult implements ResultGeneratorInterface
{
    /**
     * @var JSON $json The underlying JSON object.
     */
    private JSON $json;

    /**
     * Constructor. Sets the json object to be converted.
     * 
     * @param JSON $json The json object.
     * 
     * @return void
     */
    public function __construct(JSON $json)
    {
        $this->json = $json;
    }

    /**
     * Generates a valid HTTP response containg a body made from this JSON object.
     * 
     * @return Response The generated response
     */
    public function toResponse(): Response
    {
        $response = new Response();
        $response->addHeader("content-type", "application/json");
        $response->setBody($this->json);
        
        return $response;
    }
}
