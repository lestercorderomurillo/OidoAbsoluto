<?php

namespace Pipeline\HTTP\Common;

use Pipeline\HTTP\Message;
use Pipeline\Result\JSONResult;
use Pipeline\Result\RedirectResult;

abstract class BaseActions
{
    public function JSON($json, int $hints = 0): Message
    {
        $result = new JSONResult($json, $hints);
        return $result->toResponse();
    }

    public function redirect(string $new_path): Message
    {
        $result = new RedirectResult($new_path);
        return $result->toResponse();
    }
}
