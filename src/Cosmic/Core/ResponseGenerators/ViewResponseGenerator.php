<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\ResponseGenerators;

use Cosmic\DOM\Compiler;
use Cosmic\DOM\DOM;
use Cosmic\Core\Types\View;
use Cosmic\Core\Interfaces\ResponseGenerator;
use Cosmic\FileSystem\FS;
use Cosmic\FileSystem\Paths\FilePath;
use Cosmic\FileSystem\Paths\FolderPath;
use Cosmic\HTTP\Server\Response;
use Cosmic\Utilities\Collections;
use Cosmic\Utilities\HTML;

/**
 * A response that renders a view and generates a valid HTTP response with the result as the body.
 */
class ViewResponseGenerator implements ResponseGenerator
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
        $bundleScripts[] = new FilePath("src/Cosmic/Bundle/Packages/jquery-3.6.0/jquery.min.js");
        $bundleScripts[] = new FilePath("src/Cosmic/Bundle/Packages/popper-1.16.1/popper.min.js");
        $bundleScripts[] = new FilePath("src/Cosmic/Bundle/Packages/bootstrap-4.6.0/bootstrap.min.js");
        $bundleScripts[] = new FilePath("src/Cosmic/Bundle/Packages/observable-slim-0.1.5/observable-slim.min.js");
        $bundleScripts[] = new FilePath("src/Cosmic/Bundle/Packages/jquery-validate-1.11.1/jquery.validate.min.js");
        $bundleScripts[] = new FilePath("src/Cosmic/Bundle/Packages/canvas-js/canvasjs.min.js");
        $bundleScripts[] = new FilePath("src/Cosmic/Bundle/Packages/font-awesome-4.7.0/fa.js");

        // 2. Load all runtime scripts
        $bundleScripts = FS::toWebPaths($bundleScripts);
        $runtimeScripts = FS::lookup(new FolderPath("src/Cosmic/DOM/Runtime/"), "js");

        $scripts = Collections::merge($bundleScripts, $runtimeScripts);

        // 3. CSS from packages
        $styles = FS::lookup(new FolderPath("src/Cosmic/Bundle/Packages/"), "css");
        $styles[] = (new FilePath(__CONTENT__ . "Output/Build.css"))->toWebPath()->toString();

        // 4. Headers
        $headers =
        [
            [
                "name" => "timestamp",
                "content" => $this->view->getTimestamp()
            ],
            [
                "name" => "page",
                "content" => $this->view->getViewIdentifier()
            ]
        ];

        // 5. Inject bundles to app and start the rendering
        app()->primitive("scriptBundles", $scripts);
        app()->primitive("metaBundles", $headers);
        app()->primitive("styleBundles", $styles);

        /**
         * @var Compiler $compiler
         **/
        $compiler = app()->get(Compiler::class);

        /**
         * @var DOM $dom
         **/
        $dom = app()->get(Bindings::class);

        $html = $compiler->compileString($this->view->getSourceHTML(), $this->view->getViewData());
        $html = $compiler->compileServerSideTokens($html, ["bindings" => $dom->getOuputJavascript()]);
        $html = $compiler->compileClientSideTokens($html);

        $jsFile = new FilePath($this->view->getFolderPath() . $this->view->getViewName() . ".js");

        if (FS::exists($jsFile)){
            $html .= "\n" . HTML::encodeInJScript(FS::read($jsFile));
        }

        $response = new Response();
        $response->addHeader("Content-Type", "text/html");
        $response->setBody($html);

        return $response;
    }
}
