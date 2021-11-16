<?php

namespace Pipeline\Core\Result;

use Pipeline\Core\DI;
use Pipeline\Core\Types\View;
use Pipeline\Core\Interfaces\ResultInterface;
use Pipeline\DOM\ViewRenderer;
use Pipeline\HTTP\Server\ServerResponse;
use function Pipeline\Kernel\dependency;

class ViewResult implements ResultInterface
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function toResponse(): ServerResponse
    {
        $renderer = DI::getDependency(ViewRenderer::class);

        $renderer->setView($this->view);

        $response = new ServerResponse();
        $response->addHeader("Content-Type", "text/html");
        $response->setBody($renderer->render());

        return $response;
    }
}
