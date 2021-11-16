<?php

namespace Pipeline\Core\Boot\Controllers;

use Pipeline\Core\Boot\Actions;
use Pipeline\Core\Types\View;
use Pipeline\Core\Types\JSON;
use Pipeline\HTTP\ErrorMessage;
use Pipeline\HTTP\Message;
use Pipeline\HTTP\Server\ServerResponse;
use Pipeline\Core\Result\ViewResult;

abstract class Controller extends Actions
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

        $view = new View($this->getClassName(), $view_name, $view_data);
        $result = new ViewResult($view);
        return $result->toResponse();
    }

    public function handle($input): Message
    {
        if ($input instanceof ServerResponse) {

            return $input;

        } else if (is_string($input) || is_int($input)) {

            return $this->Content($input);

        } else if (!isset($input)) {

            return new ErrorMessage("Empty Response");
            
        }

        return new ErrorMessage("Invalid Type");
    }
}
