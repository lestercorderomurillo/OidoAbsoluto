<?php

namespace Pipeline\Controller;

use Pipeline\View\View;
use Pipeline\Result\JSONResult;
use Pipeline\Result\RedirectResult;
use Pipeline\Result\ViewResult;

abstract class Controller extends ControllerBase
{
    public function view(string $view_name = "Error", array $parameters = [])
    {
        $view = new View($this->getControllerName(), $view_name, $parameters);
        return new ViewResult($view);
    }

    public function JSON($json, int $hints = 0)
    {
        return new JSONResult($json, $hints);
    }

    public function redirect(string $new_path)
    {
        return new RedirectResult($new_path);
    }
}
