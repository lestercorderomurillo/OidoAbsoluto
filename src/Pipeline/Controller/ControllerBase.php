<?php

namespace Pipeline\Controller;

use Pipeline\Core\Types\JSON;
use Pipeline\HTTP\Message;
use Pipeline\Result\JSONResult;
use Pipeline\Result\RedirectResult;
use Pipeline\HTTP\Common\RequestHandlerInterface;

abstract class ControllerBase implements RequestHandlerInterface
{
    public static function getControllerName(): string
    {
        return static::class;
    }

    public function JSON(JSON $json): Message
    {
        $result = new JSONResult($json);
        return $result->toResponse();
    }

    public function redirect(string $new_path): Message
    {
        $result = new RedirectResult($new_path);
        return $result->toResponse();
    }
}
