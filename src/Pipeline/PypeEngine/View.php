<?php

namespace Pipeline\PypeEngine;

use Pipeline\Hotswap\ChangeDetector;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\FilePath;
use Pipeline\PypeEngine\Inproc\RenderContext;
use Pipeline\Utilities\ArrayHelper;

class View
{
    private array $view_data;
    private string $view_name;
    private string $controller_name;
    private string $html;
    private string $timestamp;

    public function __construct(string $controller_name, string $view_name, array $view_data)
    {
        $splitted = explode("\\", $controller_name);
        $controller_name = $splitted[count($splitted) - 1];
        $controller_name = str_replace("Controller", "", $controller_name);

        $this->view_name = $view_name;
        $this->view_data = $view_data;
        $this->controller_name = $controller_name;

        $this->html = FileSystem::includeAsString(new FilePath(SystemPath::VIEWS, $this->controller_name . "/" . $view_name, "phtml"));
        $this->timestamp = ChangeDetector::generateTimestampForView($this->getControllerName(), $this->getViewName());
    }

    public function getDirectory(): DirectoryPath
    {
        return new DirectoryPath(SystemPath::VIEWS, "$this->controller_name/");
    }

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    public function getSourceHTML(): string
    {
        return $this->html;
    }

    public function getViewGUID(): string
    {
        $path = new FilePath(
            SystemPath::VIEWS,
            $this->getControllerName() . "/" .
                $this->getViewName(),
            "phtml"
        );

        return md5($path->toString());
    }

    public function getViewFilePath(): FilePath
    {
        return (new FilePath(
            SystemPath::VIEWS,
            $this->view->getControllerName() . "/" .
                $this->view->getViewName(),
            "phtml"
        ));
    }

    public function getControllerName(): string
    {
        return $this->controller_name;
    }

    public function getViewName(): string
    {
        return $this->view_name;
    }

    public function getViewData(): array
    {
        return $this->view_data;
    }

    public function &addViewData(array $array): View
    {
        $this->view_data = ArrayHelper::mergeNamedValues($this->view_data, $array);
        return $this;
    }

    public function &addRenderContext(RenderContext $context): View
    {
        $this->view_data = ArrayHelper::mergeNamedValues($this->view_data, $context->expose());
        return $this;
    }

    public function getRenderContext(): RenderContext
    {
        return new RenderContext($this->view_data);
    }
}
