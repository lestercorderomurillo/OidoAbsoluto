<?php

namespace Pipeline\Controller;

use Pipeline\Core\ResultInterface;
use Pipeline\Core\Types\JSON;
use Pipeline\HTTP\Message;
use Pipeline\HTTP\InvalidMessage;
use Pipeline\HTTP\NullMessage;
use Pipeline\HTTP\Server\ServerResponse;
use Pipeline\Result\JSONResult;

abstract class ApiController extends ControllerBase
{
    public function handle($result_or_response): Message
    {
        if ($result_or_response instanceof ServerResponse) {
            return $result_or_response;
        } else if ($result_or_response instanceof ResultInterface) {
            return $result_or_response->toResponse();
        } else if (is_string($result_or_response) || is_int($result_or_response)) {
            $json = new JSON($result_or_response);
            $result = new JSONResult($json);
            return $result->toResponse();
        } else if (!isset($result_or_response)) {
            return new NullMessage();
        }

        return new InvalidMessage();
    }
}
