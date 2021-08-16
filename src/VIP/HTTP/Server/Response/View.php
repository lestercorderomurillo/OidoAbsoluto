<?php

namespace VIP\HTTP\Server\Response;

use VIP\Controller\BaseController;
use VIP\FileSystem\BasePath;
use VIP\FileSystem\FilePath;
use VIP\HTTP\Server\Response\AbstractResponse;
use VIP\FileSystem\FileSystem;
use VIP\FileSystem\DirectoryPath;
use VIP\Hotswap\ChangeDetector;

use function VIP\Core\Services;

class View extends AbstractResponse
{
    private string $controller;
    private string $name;
    private array $parameters;
    private string $html;
    private string $timestamp;

    public function __construct(string $name, array $parameters = [])
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->controller = BaseController::getCurrentControllerName();
        $this->callback_controller = BaseController::getCallbackControllerName();
        $this->html = FileSystem::includeAsString(new FilePath(BasePath::DIR_VIEWS, $this->controller . "/" . $name, "phtml"));
        $this->timestamp = ChangeDetector::generateTimestampForView($this->getControllerName(), $this->getViewName());
    }

    public function getDirectory(): DirectoryPath
    {
        return new DirectoryPath(BasePath::DIR_VIEWS, "$this->controller/");
    }

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    public function getSourceHTML(): string
    {
        return $this->html;
    }

    public function getControllerName(): string
    {
        return $this->controller;
    }

    public function getCallbackControllerName(): string
    {
        return $this->callback_controller;
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

        $this->logger->debug(
            "{0} responded with '{1}' : '{2}'",
            [
                BaseController::getFQCurrentControllerName(),
                $this->getStatusCode(),
                "(View)"
            ]
        );
    }
}
