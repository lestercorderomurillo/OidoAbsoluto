<?php

namespace Pipeline\View;

use Pipeline\Hotswap\ChangeDetector;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\BasePath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\FilePath;

class View
{
    private string $controller_name;

    private string $view_name;
    private array $view_data;

    private string $html;
    private string $timestamp;

    public function __construct(string $controller_name, string $view_name, array $view_data)
    {

        $splitted = explode("\\", $controller_name);
        $controller_name = $splitted[count($splitted) - 1];
        $controller_name = str_replace("Controller", "", $controller_name);
        
        $this->controller_name = $controller_name;
        $this->view_name = $view_name;
        $this->view_data = $view_data;

        $this->html = FileSystem::includeAsString(new FilePath(BasePath::DIR_VIEWS, $this->controller_name . "/" . $view_name, "phtml"));
        $this->timestamp = ChangeDetector::generateTimestampForView($this->getControllerName(), $this->getViewName());
    }

    public function getDirectory(): DirectoryPath
    {
        return new DirectoryPath(BasePath::DIR_VIEWS, "$this->controller_name/");
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
}
