<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Abstracts;

use Cosmic\Core\Types\View;
use Cosmic\Core\Types\JSON;
use Cosmic\HTTP\Server\Response;
use Cosmic\ORM\Abstracts\Model;
use Cosmic\Core\ResponseGenerators\ContentResponseGenerator;
use Cosmic\Core\ResponseGenerators\JSONResponseGenerator;
use Cosmic\Core\ResponseGenerators\RedirectResponseGenerator;
use Cosmic\Core\ResponseGenerators\ViewResponseGenerator;
use Cosmic\Utilities\Collections;
use Cosmic\Utilities\Strings;

/**
 * This class represents actions that can be performed by a controller.
 */
abstract class Controller
{
    /**
     * Return the current controller class name. If the controller is 
     * 
     * @return string
     */
    protected static function getControllerName(): string
    {
        $name = static::class;

        if (!Strings::contains($name, ["Controller"])) {
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
            $result = new JSONResponseGenerator($json, 0);
        } else {
            $result = new JSONResponseGenerator(new JSON($json), 0);
        }

        return $result->toResponse();
    }

    /**
     * Returns a compiled view result. Internally will use Cosmic DOM to render this view.
     * If both parameters are left empty, the controller will assume the view is called the same as the function name.
     * 
     * @param string|array|Model $dynamicValue If it's a string, it will be considered as the view name. If it's an array or a model, 
     * it will be considered as the view data. Then the second parameter can be left blank.
     * @param string|array|Model $dynamicValueSecondary If the first parameter was the view name, this parameter can be used as view data.
     * @return Response A valid HTTP response object with a rendered view.
     */
    public function view($dynamicValue = null, $dynamicValueSecondary = null): Response
    {
        if (is_string($dynamicValue)) {
            $viewName = $dynamicValue;
        } else {
            $viewName = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        }

        if (is_array($dynamicValue) || $dynamicValue instanceof Model) {
            $viewData = $dynamicValue;
        } else if (is_array($dynamicValueSecondary) || $dynamicValueSecondary instanceof Model) {
            $viewData = $dynamicValueSecondary;
        } else {
            $viewData = [];
        }

        if ($viewData instanceof Model) {

            $viewData = $viewData->getValues();
        }

        foreach ($viewData as $key => $value) {

            if ($value instanceof Model) {
                
                $value = $value->getValues();
                
                if (Collections::isList($value)) {

                    $viewData[$key] = Collections::encodeArraytoString($value);
                    
                } else {

                    $tokens = Collections::tokenize($key, $value);

                    foreach ($tokens as $_key => $_value) {
                        $viewData[$_key] = $_value;
                    }

                    $viewData[$key] = Collections::encodeArraytoString($tokens);
                }
            }

            if ($value instanceof JSON) {
                $viewData[$key] = $value->toString();
            }

            if (is_array($value)) {

                if (Collections::typeOf(Model::class, $value)) {

                    $values = [];

                    /** @var Model[] $input */
                    foreach ($value as $model) {
                        $values[] = $model->getValues();
                    }

                    $viewData[$key] = Collections::encodeArraytoString($values);

                } else {

                    if (Collections::isList($value)) {

                        $viewData[$key] = Collections::encodeArraytoString($value);

                    } else {

                        $tokens = Collections::tokenize($key, $value);
                        foreach ($tokens as $_key => $_value) {
                            $viewData[$_key] = $_value;
                        }

                        $viewData[$key] = Collections::encodeArraytoString($tokens);
                    }
                }
            }
        }

        $view = new View($this->getControllerName(), $viewName, $viewData);
        $result = new ViewResponseGenerator($view);

        return $result->toResponse();
    }

    /**
     * Download a file to the client browser.
     * 
     * @param string $fileName The file name.
     * @param string $contentType The type of content to be downloaded.
     * @param string $content The content to be downloaded.
     * @return void
     */
    protected function download(string $fileName, string $contentType, string $content, bool $buffered = false): void
    {
        header("Content-Type: $contentType");
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        
        if($buffered === true){
            readfile($fileName);
            unlink($fileName);
        }else{
            file_put_contents('php://output', $content);
        }

        die();
    }

    /**
     * Returns a simple content response from a string, assuming 200 OK.
     * 
     * @param string $input The input string to display.
     * @return Response A response instance with plain text content.
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
     * @return Response A response instance with plain text content.
     */
    protected function response(string $code, string $input): Response
    {
        $result = new ContentResponseGenerator($input);
        $response = $result->toResponse();
        $response->setStatusCode($code);

        return $response;
    }

    /**
     * Performs a redirect to the target location.
     * @return Response A response object with a redirect.
     */
    protected function redirect(string $targetURL = "index"): Response
    {
        $result = new RedirectResponseGenerator($targetURL);
        return $result->toResponse();
    }

    /**
     * Displays a message on the PersistentMessage system component.
     * Uses a session internally to manage the state between requests.
     * 
     * @param string $message The message to display.
     * @param string $type The type of error. (This is css based)
     * @return void
     */
    protected function display(string $message, string $type): void
    {
        session("alertType", $type);
        session("alertText", $message);
    }

    /**
     * Displays a warning message on the PersistentMessage system component.
     * 
     * @param string $message The message to display.
     * @return void
     */
    protected function warning(string $message): void
    {
        $this->display($message, "Warning");
    }

    /**
     * Displays a success message on the PersistentMessage system component.
     * 
     * @param string $message The message to display.
     * @return void
     */
    protected function success(string $message): void
    {
        $this->display($message, "Success");
    }

    /**
     * Displays a info message on the PersistentMessage system component.
     * 
     * @param string $message The message to display.
     * @return void
     */
    protected function info(string $message): void
    {
        $this->display($message, "Info");
    }

    /**
     * Displays a error message on the PersistentMessage system component.
     * 
     * @param string $message The message to display.
     * @return void
     */
    protected function error(string $message): void
    {
        $this->display($message, "Error");
    }
}
