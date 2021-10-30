<?php

namespace Pipeline\Core\Boot;

use Pipeline\Core\Boot\ActionsBase;
use Pipeline\Core\Types\JSON;
use Pipeline\HTTP\Message;
use Pipeline\HTTP\InvalidMessage;
use Pipeline\HTTP\EmptyMessage;
use Pipeline\HTTP\Server\ServerResponse;

abstract class ApiControllerBase extends ActionsBase
{
    public function handle($input): Message
    {
        if ($input instanceof ServerResponse) {

            return $input;

        } else if (is_string($input) || is_int($input)) {

            return $this->JSON(new JSON($input));

        } else if (!isset($input)) {

            return new EmptyMessage();
            
        }

        return new InvalidMessage();
    }
}
