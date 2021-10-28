<?php

namespace Pipeline\Core;

use Pipeline\Core\Types\JSON;
use Pipeline\Core\Types\XML;
use Pipeline\Core\Facade\RequestHandlerInterface;
use Pipeline\HTTP\Message;
use Pipeline\Result\ContentResult;
use Pipeline\Result\JSONResult;
use Pipeline\Result\RedirectResult;
use Pipeline\Result\XMLResult;

use function Pipeline\Navigate\session;

abstract class ControllerBase implements RequestHandlerInterface
{
    public static function getControllerName(): string
    {
        return static::class;
    }

    public function discardSessionErrors(): void
    {
        $this->discardSessionKeys([
            "error" => null,
            "error-type" => null
        ]);
    }

    public function discardSessionKeys(array $keys): void
    {
        foreach (session()->expose() as $key => $value) {
            if (array_key_exists($key, $keys)) {
                session()->remove($key);
            }
        }
    }

    public function Content(string $input): Message
    {
        $result = new ContentResult($input);
        return $result->toResponse();
    }

    public function JSON(JSON $json): Message
    {
        $result = new JSONResult($json);
        return $result->toResponse();
    }

    public function XML(XML $xml): Message
    {
        $result = new XMLResult($xml);
        return $result->toResponse();
    }

    public function redirect(string $new_path): Message
    {
        $result = new RedirectResult($new_path);
        return $result->toResponse();
    }
}
