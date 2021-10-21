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
    public function handle($input): Message
    {
        if ($input instanceof ServerResponse) {
            return $input;
        } else if ($input instanceof ResultInterface) {
            return $input->toResponse();
        } else if (is_string($input) || is_int($input)) {
            $json = new JSON($input);
            $result = new JSONResult($json);
            return $result->toResponse();
        } else if (!isset($input)) {
            return new NullMessage();
        }

        return new InvalidMessage();
    }
}
