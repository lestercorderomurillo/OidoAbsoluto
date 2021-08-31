<?php

namespace Pipeline\Result;

use Pipeline\Pype\View;
use Pipeline\Pype\ViewRenderer;
use Pipeline\Core\ResultInterface;
use Pipeline\HTTP\Server\ServerResponse;
use function Pipeline\Accessors\Dependency;

class ViewResult implements ResultInterface
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function toResponse(): ServerResponse
    {
        $view_renderer = Dependency(ViewRenderer::class);
        $view_renderer->setView($this->view);
        $response = new ServerResponse();
        $response->addHeader("Content-Type", "text/html");
        $response->setBody($view_renderer->renderView());
        return $response;
    }
}
