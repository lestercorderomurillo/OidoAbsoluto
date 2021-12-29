<?php

namespace Cosmic\Core\Result;

use Cosmic\Binder\Compiler;
use Cosmic\Core\Types\View;
use Cosmic\Core\Interfaces\ResultGeneratorInterface;
use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\File;
use Cosmic\FileSystem\Paths\Folder;
use Cosmic\HTTP\Server\Response;
use Cosmic\Utilities\Collection;

use function Cosmic\Core\Bootstrap\app;

/**
 * A response that renders a view and generates a valid HTTP response with the result as the body.
 */
class ViewResult implements ResultGeneratorInterface
{
    /**
     * @var View $view The view to render.
     */
    private View $view;

    /**
     * Construtor. Store the view. When converted to an response, the view will be rendered.
     * 
     * @param View $view The view to be rendered later on. 
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * Generates a valid HTTP response with the rendered view as their body.
     * 
     * @return Response The generated response.
     */
    public function toResponse(): Response
    {

        // 1. Load the packages in the order they are needed.
        $bundleScripts = [];
        $bundleScripts[] = new File("src/Cosmic/Bundle/Packages/jquery-3.6.0/jquery.min.js");
        $bundleScripts[] = new File("src/Cosmic/Bundle/Packages/popper-1.16.1/popper.min.js");
        $bundleScripts[] = new File("src/Cosmic/Bundle/Packages/bootstrap-4.6.0/bootstrap.min.js");
        $bundleScripts[] = new File("src/Cosmic/Bundle/Packages/observable-slim-0.1.5/observable-slim.min.js");
        $bundleScripts[] = new File("src/Cosmic/Bundle/Packages/jquery-validate-1.11.1/jquery.validate.min.js");
        $bundleScripts[] = new File("src/Cosmic/Bundle/Packages/canvas-js/canvasjs.min.js");

        // 2. Load all runtime scripts
        $bundleScripts = FileSystem::toWebPaths($bundleScripts);
        $runtimeScripts = FileSystem::URLFind(new Folder("src/Cosmic/Binder/Runtime/"), "js");

        $scripts = Collection::mergeList($bundleScripts, $runtimeScripts);

        // 3. CSS from packages
        $styles = FileSystem::URLFind(new Folder("src/Cosmic/Bundle/Packages/"), "css");
        $styles[] = (new File(__CONTENT__ . "output/build.css"))->toWebPath()->toString();

        // 4. Headers
        $headers =
        [
            [
                "name" => "timestamp",
                "content" => $this->view->getTimestamp()
            ],
            [
                "name" => "page",
                "content" => $this->view->getViewGUID()
            ]
        ];

        // 5. Inject bundles to app and start the rendering
        app()->injectPrimitive("scriptBundles", $scripts);
        app()->injectPrimitive("metaBundles", $headers);
        app()->injectPrimitive("styleBundles", $styles);

        $compiler = app()->get(Compiler::class);
        $html = $compiler->compileString($this->view->getSourceHTML(), $this->view->getViewData());

        $response = new Response();
        $response->addHeader("Content-Type", "text/html");
        $response->setBody($html);

        return $response;
    }
}
