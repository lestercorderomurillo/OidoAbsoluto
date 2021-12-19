<?php

namespace Cosmic\Core\Interfaces;

use Cosmic\HTTP\Server\Response;

/**
 * This class represents objects that can be converted to HTTP responses.
 */
interface ResultGeneratorInterface
{
    /**
     * Converts the result to a valid HTTP response.
     * 
     * @return Response
     */
    public function toResponse(): Response;
}
