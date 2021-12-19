<?php

namespace Cosmic\Core\Result;

use Cosmic\Core\Types\View;
use Cosmic\Core\Interfaces\ResultGeneratorInterface;
use Cosmic\HTTP\Server\Response;

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
        echo("ViewResult.php/toResponse()<br>");
        die($this->view->getViewPath());

        /*$renderer = app()->get(ViewRenderer::class);
        $renderer->setView($this->view);

        $response = new Response();
        $response->addHeader("Content-Type", "text/html");
        $response->setBody($renderer->render());

        return $response;*/
    }
}
