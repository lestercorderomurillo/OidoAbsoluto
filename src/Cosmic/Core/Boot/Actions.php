<?php

namespace Cosmic\Core\Boot;

use Cosmic\Core\Types\JSON;
use Cosmic\HTTP\Server\Response;
use Cosmic\Core\Result\ContentResult;
use Cosmic\Core\Result\JSONResult;
use Cosmic\Core\Result\RedirectResult;

/**
 * This class represents actions that can be performed.
 */
abstract class Actions
{
    /**
     * Return the current runtime class name.
     * 
     * @return string
     */
    protected static function getClassName(): string
    {
        return static::class;
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
        session("alert-type", $type);
        session("alert-text", $message);
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
        $this->display($message, "warning");
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
        $this->display($message, "success");
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
        $this->display($message, "danger");
    }

    /**
     * Performs a redirect to the target location.
     * 
     * @return Response
     */
    protected function redirect(string $targetURL): Response
    {
        /*if (Text::endsWith($targetURL, "/")) {
            $targetURL = substr($targetURL, -1);
        }*/

        $result = new RedirectResult($targetURL);
        return $result->toResponse();
    }
}
