<?php

namespace Cosmic\Core\Bootstrap;

use Cosmic\Core\Types\View;
use Cosmic\Core\Types\JSON;
use Cosmic\HTTP\Server\Response;
use Cosmic\Core\Result\ContentResult;
use Cosmic\Core\Result\JSONResult;
use Cosmic\Core\Result\RedirectResult;
use Cosmic\Core\Result\ViewResult;
use Cosmic\Utilities\Text;

/**
 * This class represents actions that can be performed by a controller.
 */
abstract class Controller
{
    /**
     * Return the current controller class name.
     * 
     * @return string
     */
    protected static function getControllerName(): string
    {
        $name = static::class;
        if (Text::contains($name, ["Router"])) {
            return "System";
        }
        return $name;
    }

    /**
     * Creates a new JSON response from anything.
     * 
     * @param mixed $json Can be either a JSON object or an associative array or anything.
     * It will be automatically converted to a valid JSON object.
     * 
     * @return Response
     */
    protected function JSON($json): Response
    {
        if ($json instanceof JSON) {
            $result = new JSONResult($json, 0);
        } else {
            $result = new JSONResult(new JSON($json), 0);
        }

        return $result->toResponse();
    }

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

        $view = new View($this->getControllerName(), $viewName, $viewData);
        $result = new ViewResult($view);

        return $result->toResponse();
    }

    /**
     * Returns a simple content response from a string.
     * 
     * @param string $input The input string to display.
     * 
     * @return Response
     */
    protected function content(string $input): Response
    {
        return $this->response(200, $input);
    }

    /**
     * Creates and displays a new HTTP response.
     * 
     * @param string $code The number code to use in HTTP.
     * @param string $input The input string to display.
     * 
     * @return Response
     */
    protected function response(string $code, string $input): Response
    {
        $result = new ContentResult($input);

        $response = $result->toResponse();
        $response->setStatusCode($code);

        return $response;
    }

    /**
     * Displays a message on the PersistentError system component.
     * Uses a session internally to manage the state between requests.
     * 
     * @param string $message The message to display.
     * @param string $type The type of error. (This is css based)
     * 
     * @return void
     */
    protected function display(string $message, string $type): void
    {
        session("alertType", $type);
        session("alertText", $message);
    }

    /**
     * Displays a warning message on the PersistentError system component.
     * 
     * @param string $message The message to display.
     * 
     * @return void
     */
    protected function warning(string $message): void
    {
        $this->display($message, "Warning");
    }

    /**
     * Displays a success message on the PersistentError system component.
     * 
     * @param string $message The message to display.
     * 
     * @return void
     */
    protected function success(string $message): void
    {
        $this->display($message, "Success");
    }

    /**
     * Displays a danger message on the PersistentError system component.
     * 
     * @param string $message The message to display.
     * 
     * @return void
     */
    protected function danger(string $message): void
    {
        $this->display($message, "Danger");
    }

    /**
     * Performs a redirect to the target location.
     * 
     * @return Response
     */
    protected function redirect(string $targetURL = "index"): Response
    {
        $result = new RedirectResult($targetURL);
        return $result->toResponse();
    }
}