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
    public function view($dynamic_value = null, $dynamic_value_secondary = null): Message
    {

        if(is_string($dynamic_value)){
            $view_name = $dynamic_value;
        }else{
            $view_name = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        }

        if(is_array($dynamic_value)){
            $view_data = $dynamic_value;
        }else if(is_array($dynamic_value_secondary)) {
            $view_data = $dynamic_value_secondary;
        }else{
            $view_data = [];
        }

        foreach($view_data as $key => $value){
            if($value instanceof JSON){
                $view_data[$key] = $value->toString();
            }
        }

        $view = new View($this->getControllerName(), $view_name, $view_data);
        $result = new ViewResult($view);
        return $result->toResponse();
    }

    public function handle($input): Message
    {
        if ($input instanceof ServerResponse) {
            return $input;
        } else if ($input instanceof ResultInterface) {
            return $input->toResponse();
        } else if (is_string($input) || is_int($input)) {
            $result = new ContentResult($input);
            return $result->toResponse();
        } else if (!isset($input)) {
            return new NullMessage();
        }

        return new InvalidMessage();
    }
}
