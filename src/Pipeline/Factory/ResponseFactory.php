<?php

namespace Pipeline\Factory;

use Pipeline\App\App;
use Pipeline\View\View;
use Pipeline\Logger\Logger;
use Pipeline\HTTP\Message;
use Pipeline\Renderer\ViewRenderer;
use Pipeline\HTTP\Server\ServerResponse;

use function Pipeline\Accessors\App;
use function Pipeline\Accessors\Dependency;

class ResponseFactory
{
    public static function createServerResponse(int $code = 200, string $message = ""): ServerResponse
    {
        App()->getRuntimeEnvironment()->notifyFailure();

        die($message);

        if ($message == "") {
            $message = Message::CODES[$code];
        }

        if ($code >= 500) {
            Dependency(Logger::class)->error("{0} responded with '{1}'", [$code, $message]);
        }

        $response = new ServerResponse($code);
        $response->addHeader("Content-Type", "text/html");

        $response_view = new View("System", "response", ["code" => "$code", "message" => "$message"]);

        $view_renderer = Dependency(ViewRenderer::class);
        $view_renderer->setView($response_view);
        $html = $view_renderer->renderView();

        $response->setBody($html);
        return $response;
    }
}
