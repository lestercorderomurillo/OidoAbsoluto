<?php

namespace Pipeline\Result;

use Pipeline\Core\ResultInterface;
use Pipeline\HTTP\Server\ServerResponse;
use Pipeline\PypeEngine\View;
use Pipeline\PypeEngine\PypeViewRenderer;

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
        $renderer = Dependency(PypeViewRenderer::class);
        $renderer->setView($this->view);

        $response = new ServerResponse();
        $response->addHeader("Content-Type", "text/html");
        $response->setBody($renderer->render());

        return $response;
    }
}
