<?php

namespace Pipeline\Core\Boot\Controllers;

use Pipeline\Core\Boot\Actions;
use Pipeline\Core\Types\JSON;
use Pipeline\HTTP\Message;
use Pipeline\HTTP\ErrorMessage;
use Pipeline\HTTP\Server\ServerResponse;

abstract class ApiController extends Actions
{
    public function handle($input): Message
    {
        if ($input instanceof ServerResponse) {

            return $input;

        } else if (is_string($input) || is_int($input)) {

            return $this->JSON(new JSON($input));

        } else if (!isset($input)) {

            return new ErrorMessage("Empty Response");
            
        }

        return new ErrorMessage("Invalid Type");
    }
}
