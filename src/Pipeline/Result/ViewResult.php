<?php

namespace Pipeline\Result;

use Pipeline\View\View;
use Pipeline\Core\ResultInterface;
use Pipeline\HTTP\Server\ServerResponse;
use Pipeline\Logger\Logger;
use Pipeline\Renderer\ViewRenderer;
use function Pipeline\Accessors\Dependency;

class ViewResult implements ResultInterface
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function handle(): void
    {
        $view_renderer = Dependency(ViewRenderer::class);
        $view_renderer->setView($this->view);
        $html = $view_renderer->renderView();

        $response = new ServerResponse();
        $response->addHeader("Content-Type", "text/html");
        $response->setBody($html);
        $response->send();

        Dependency(Logger::class)->debug("{0} responded with '{1}' : '{2}'", [static::class, $response->getStatusCode(), self::class]);
    }
}
