<?php

namespace Pipeline\Controller;

use Pipeline\Core\ResultInterface;
use Pipeline\Core\Types\JSON;
use Pipeline\HTTP\InvalidMessage;
use Pipeline\HTTP\Message;
use Pipeline\HTTP\NullMessage;
use Pipeline\HTTP\Server\ServerResponse;
use Pipeline\PypeEngine\View;
use Pipeline\Result\ContentResult;
use Pipeline\Result\ViewResult;

abstract class Controller extends ControllerBase
{
    public function view(string $view_name = "", array $parameters = []): Message
    {
        if ($view_name == "") {
            $view_name = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        }

        foreach($parameters as $key => $value){
            if($value instanceof JSON){
                $value = $value->toString();
            }
            $parameters["view:" . $key] = $value;
            unset($parameters[$key]);
        }

        $view = new View($this->getControllerName(), $view_name, $parameters);
        $result = new ViewResult($view);
        return $result->toResponse();
    }

    public function handle($result_or_response): Message
    {
        if ($result_or_response instanceof ServerResponse) {
            return $result_or_response;
        } else if ($result_or_response instanceof ResultInterface) {
            return $result_or_response->toResponse();
        } else if (is_string($result_or_response) || is_int($result_or_response)) {
            $result = new ContentResult($result_or_response);
            return $result->toResponse();
        } else if (!isset($result_or_response)) {
            return new NullMessage();
        }

        return new InvalidMessage();
    }
}
