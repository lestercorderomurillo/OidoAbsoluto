<?php

namespace Pipeline\HTTP\Server;

use Pipeline\HTTP\Message;
use Pipeline\Pype\View;
use Pipeline\Pype\ViewRenderer;
use function Pipeline\Accessors\App;
use function Pipeline\Accessors\Dependency;

class ServerResponse extends Message
{
    public static function create(int $code = 200, string $message = ""): ServerResponse
    {
        if ($message == "") {
            $message = Message::CODES[$code];
        }

        if ($code >= 500) {
            App()->getRuntimeEnvironment()->notifyFailure();
            Dependency(Logger::class)->error("{0} responded with '{1}'", [$code, $message]);
        }

        $response = new ServerResponse($code);
        $response->addHeader("Content-Type", "text/html");

        $response_view = new View("System", "response", ["code" => "$code", "message" => "$message"]);

        $view_renderer = Dependency(ViewRenderer::class);
        $view_renderer->setView($response_view);

        $old_view = $view_renderer->getContextlessView();
        $view_renderer->addMetaTags(["timestamp" => $old_view->getTimestamp(), "page" => $old_view->getViewHashIdentifier()]);
        $html = $view_renderer->renderView();

        $response->setBody($html);
        return $response;
    }

    public function __construct(int $status_code = 200, string $protocol_version = "1.1")
    {
        parent::__construct($status_code, $protocol_version);
    }

    public function sendAndExit()
    {
        $this->send();
        exit();
    }

    public function send()
    {
        if (!headers_sent()) {
            header("HTTP/$this->protocol_version: $this->status_code" . Message::CODES[$this->status_code]);

            foreach ($this->headers as $key => $values) {
                foreach ($values as $value) {
                    header("$key: $value", false);
                }
            }

            if ($this->getBody() != "") {
                echo ($this->getBody());
            }
        }
    }
}
