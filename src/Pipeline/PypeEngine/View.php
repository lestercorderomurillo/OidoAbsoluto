<?php

namespace Pipeline\PypeEngine;

use Pipeline\Hotswap\ChangeDetector;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\FilePath;
use Pipeline\Security\Cryptography;
use Pipeline\Utilities\ArrayHelper;

class View
{
    private string $view_name;
    private string $controller_name;

    private string $html;
    private string $timestamp;

    private array $context;

    public function __construct(string $controller_name, string $view_name, $context = null)
    {
        $splitted = explode("\\", $controller_name);
        $controller_name = $splitted[count($splitted) - 1];
        $controller_name = str_replace("Controller", "", $controller_name);

        $this->view_name = $view_name;
        $this->controller_name = $controller_name;

        $this->html = FileSystem::includeAsString(new FilePath(SystemPath::VIEWS, $this->controller_name . "/" . $view_name, "phtml"));
        $this->timestamp = ChangeDetector::generateTimestampForView($this->getControllerName(), $this->getViewName());

        $this->context = $this->getBuildinContext();

        if($context != null){
            $this->context = ArrayHelper::mergeNamedValues($this->context, $context);
        }
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

    public function &addContext($context): View
    {
        $this->context = ArrayHelper::mergeNamedValues($this->context, $context);
        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getBuildinContext()
    {
        $packages = [];
        $scripts = [];
        $styles = [];

        $packages[] = new FilePath(SystemPath::PACKAGES, "jquery-3.6.0/jquery", "min.js");
        $packages[] = new FilePath(SystemPath::PACKAGES, "popper-1.16.1/popper", "min.js");
        $packages[] = new FilePath(SystemPath::PACKAGES, "bootstrap-4.6.0/bootstrap", "min.js");
        $packages[] = new FilePath(SystemPath::PACKAGES, "observable-slim-0.1.5/observable-slim", "min.js");
        $packages[] = new FilePath(SystemPath::PACKAGES, "jquery-validate-1.11.1/jquery.validate", "min.js");

        $styles[] = "https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100&display=swap";
        $styles[] = new FilePath(SystemPath::PACKAGES, "bootstrap-4.6.0/bootstrap", "css");
        $styles[] = new FilePath(SystemPath::PACKAGES, "font-awesome-4.7.0/font-awesome", "css");
        $styles[] = new FilePath(SystemPath::WEB, "build", "css");

        $scripts = ArrayHelper::stackLines($packages, FileSystem::findWebPaths(new DirectoryPath(SystemPath::BUILDIN, "Scripts"), "js"));

        $view_script = (new FilePath(SystemPath::VIEWS, $this->getControllerName() . "/" . $this->getViewName(), "js"))->toWebPath()->toString();

        $data = [
            "url" => __URL__,
            "random" => Cryptography::computeRandomKey(8),
            "headers" =>
            [
                [
                    "name" => "timestamp",
                    "content" => $this->getTimestamp()
                ],
                [
                    "name" => "page",
                    "content" => $this->getViewGUID()
                ]
            ],
            "scripts" => FileSystem::toWebPaths($scripts),
            "styles" => FileSystem::toWebPaths($styles),
            "base_script" => $view_script
        ];

        foreach($data as $key => $value){
            $data["view." . $key] = $value;
            unset($data[$key]);
        }

        return $data;
    }
}
