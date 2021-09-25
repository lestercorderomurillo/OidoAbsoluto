<?php

namespace Pipeline\Result;

use Pipeline\Pype\ViewRenderer;
use Pipeline\Core\ResultInterface;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\FilePath;
use Pipeline\HTTP\Server\ServerResponse;
use Pipeline\PypeEngine\PypeCompiler;
use Pipeline\PypeEngine\View;
use Pipeline\PypeEngine\PypeViewRenderer;
use Pipeline\Utilities\ArrayHelper;

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
        $mode = 1;

        if ($mode == 1) {
            
            $renderer = Dependency(PypeViewRenderer::class);

            $default = PypeCompiler::getDefaultViewData($this->view);
            $this->view->addViewData($default);

            $renderer->setView($this->view);

            $response = new ServerResponse();
            $response->addHeader("Content-Type", "text/html");
            $response->setBody($renderer->render());

        } else {

            $view_renderer = Dependency(ViewRenderer::class);
            $view_renderer->setView($this->view);
            $response = new ServerResponse();

            $response->addHeader("Content-Type", "text/html");
            $response->setBody($view_renderer->renderView());

        }

        return $response;
    }
}
