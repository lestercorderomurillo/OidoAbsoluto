<?php

namespace Cosmic\Core\Controllers;

use Cosmic\Core\Bootstrap\Actions;
use Cosmic\Core\Types\View;
use Cosmic\Core\Types\JSON;
use Cosmic\HTTP\Server\Response;
use Cosmic\Core\Result\ViewResult;

/**
 * This class represents a simple action controller.
 */
abstract class Controller extends Actions
{
    /**
     * Returns a compiled view result. Internally will use Cosmic Binder to render this view.
     * If both parameters are left empty, the controller will assume the view is called the same as the function name.
     * 
     * @param string|array $dynamicValue If it's a string, it will be considered as the view name. If it's an array, 
     * it will be considered as the view data. Then the second parameter can be left blank.
     * 
     * @param string|array $dynamicValueSecondary If the first parameter was the view name, this parameter can be used as view data.
     * 
     * @return Response A valid HTTP response object with a rendered view.
     */
    public function view($dynamicValue = null, $dynamicValueSecondary = null): Response
    {
        if (is_string($dynamicValue)) {
            $viewName = $dynamicValue;
        } else {
            $viewName = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        }

        if (is_array($dynamicValue)) {
            $viewData = $dynamicValue;
        } else if (is_array($dynamicValueSecondary)) {
            $viewData = $dynamicValueSecondary;
        } else {
            $viewData = [];
        }

        foreach ($viewData as $key => $value) {
            if ($value instanceof JSON) {
                $viewData[$key] = $value->toString();
            }
        }

        $view = new View($this->getClassName(), $viewName, $viewData);
        $result = new ViewResult($view);

        return $result->toResponse();
    }
}
