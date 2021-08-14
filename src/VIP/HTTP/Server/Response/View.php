<?php

namespace VIP\HTTP\Server\Response;

use VIP\Core\BaseController;
use VIP\HTTP\Server\Response\AbstractResponse;
use VIP\FileSystem\FileSystem;

use function VIP\Core\Services;

class View extends AbstractResponse
{
    private string $controller;
    private string $name;
    private array $parameters;
    private string $html;

    public function __construct(string $name, array $parameters = [])
    {
        $this->controller = BaseController::getCurrentControllerName();
        $this->name = $name;
        $this->parameters = $parameters;
        $this->html = FileSystem::includeAsString(__RDIR__ . "/app/views/" . $this->controller . "/" . $name . ".phtml");
    }

    public function getDirectoryName(): string
    {
        return __RDIR__ . "/app/views/" . $this->controller . "/";
    }

    public function getSourceHTML(): string
    {
        return $this->html;
    }

    public function getControllerName(): string
    {
        return $this->controller;
    }

    public function getViewName(): string
    {
        return $this->name;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    protected function handleOperation()
    {
        $view_renderer = Services("ViewRenderer");
        $view_renderer->setView($this);
        $view_renderer->execute();
    }
}
