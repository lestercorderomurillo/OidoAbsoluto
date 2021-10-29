<?php

namespace Pipeline\Core;

use Pipeline\Core\Types\XML;
use Pipeline\Core\Facade\RequestHandlerInterface;
use Pipeline\HTTP\Message;
use Pipeline\Result\ContentResult;
use Pipeline\Result\XMLResult;

use function Pipeline\Kernel\session;

abstract class ControllerBase extends ActionsBase implements RequestHandlerInterface
{
    public static function getControllerName(): string
    {
        return static::class;
    }

    public function content(string $input): Message
    {
        $result = new ContentResult($input);
        return $result->toResponse();
    }

    public function XML(XML $xml): Message
    {
        $result = new XMLResult($xml);
        return $result->toResponse();
    }
}
