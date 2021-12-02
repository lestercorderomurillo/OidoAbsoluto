<?php

namespace Cosmic\HTTP\Server;

use Cosmic\Core\DI;
use Cosmic\Core\Types\View;
use Cosmic\DOM\ViewRenderer;
use Cosmic\HTTP\Message;
use function Cosmic\Kernel\app;
use function Cosmic\Kernel\debug;
use function Cosmic\Kernel\dependency;

class ServerResponse extends Message
{
    public static function create(int $code = 200, string $message = ""): ServerResponse
    {
        if ($message == "") {
            $message = Message::CODES[$code];
        }

        if ($code >= 500) {
            app()->getRuntimeEnvironment()->notifyFailure();
            debug("{0} responded with '{1}'", [$code, $message]);
        }

        $response = new ServerResponse($code);
        $response->addHeader("Content-Type", "text/html");
        $response_view = new View("System", "response", ["code" => "$code", "message" => "$message"]);

        $view_renderer = DI::getDependency(ViewRenderer::class);
        $view_renderer->setView($response_view);

        /*$old_view = $view_renderer->getContextlessView();
        if(!app()->getRuntimeEnvironment()->inProductionMode()){
            $view_renderer->addMetaTags(["timestamp" => $old_view->getTimestamp(), "page" => $old_view->getViewGUID()]);
        }

        $html = $view_renderer->renderView();*/

        $response->setBody($view_renderer->render());
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
