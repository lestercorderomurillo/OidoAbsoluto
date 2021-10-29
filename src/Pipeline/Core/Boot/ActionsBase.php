<?php

namespace Pipeline\Core\Boot;

use Pipeline\Core\Types\JSON;
use Pipeline\HTTP\Message;
use Pipeline\Result\JSONResult;
use Pipeline\Result\RedirectResult;
use Pipeline\Utilities\Text;
use function Pipeline\Kernel\session;

abstract class ActionsBase
{
    public function JSON($json): Message
    {
        if ($json instanceof JSON) {
            $result = new JSONResult($json, 0);
        } else {
            $result = new JSONResult(new JSON($json), 0);
        }

        return $result->toResponse();
    }

    public function alert(string $message, string $type = "warning")
    {
        session("alert-type", $type);
        session("alert-text", $message);
    }

    public function redirect(string $target_url): Message
    {
        if (Text::endsWith($target_url, "/")) {
            $target_url = substr($target_url, 1);
        }

        $result = new RedirectResult($target_url);
        return $result->toResponse();
    }
}
