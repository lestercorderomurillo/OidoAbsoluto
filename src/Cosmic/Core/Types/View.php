<?php

namespace Cosmic\Core\Types;

use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\File;
use Cosmic\FileSystem\Paths\Folder;
use Cosmic\Reload\ChangeDetector;
use Cosmic\Utilities\Text;

/**
 * This class represents a COSMIC view.
 */
class View
{
    /**
     * @var string $viewName The name of this view.
     */
    private string $viewName;

    /**
     * @var array $viewData The stored data of this view.
     */
    private array $viewData;

    /**
     * @var string $controllerName The controller that will be used to render this view.
     */
    private string $controllerName;

    /**
     * @var string $html The body content of the view.
     */
    private string $html;

    /**
     * @var string $timestamp A time mark to use to determine file changes.
     */
    private string $timestamp;

    /**
     * Constructor.
     * 
     * @param string $controllerName The controller who will handle this view.
     * @param string $viewName Name of the view to be rendered.
     * @param array $viewData Data that will be passed down to the view as tokens.
     * 
     * @return void
     */
    public function __construct(string $controllerName, string $viewName, array $viewData)
    {
        $controllerName = Text::getNamespaceBaseName(str_replace("Controller", "", $controllerName));

        $this->controllerName = $controllerName;
        $this->viewName = $viewName;
        $this->viewData = $viewData;

        $file = new File("App/Views/$this->controllerName/$this->viewName.phtml");

        $this->html = FileSystem::read($file);
        $this->timestamp = filemtime($file);
    }

    /**
     * Returns the directory that holds the view in the file system.
     * 
     * @return Folder The directory.
     */
    public function getFolder(): Folder
    {
        return new Folder("App/Views/$this->controllerName/");
    }

    /**
     * Returns the current timestamp for this view.
     * 
     * @return string The current timestamp.
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * Returns the static source Binder/HTML markup for this view.
     * 
     * @return string The HTML.
     */
    public function getSourceHTML(): string
    {
        return $this->html;
    }

    /**
     * Returns stored view data. Can be anything from the controller, but the outer scope will always be an array.
     * 
     * @return mixed[] All stored view data on this view.
     */
    public function getViewData(): array
    {
        return $this->viewData;
    }

    /**
     * Returns an unique identifier for this view with this controller.
     * 
     * @return string A unique MD5 Identifier for this view.
     */
    public function getViewIdentifier(): string
    {
        return md5(new File("App/Views/$this->controllerName/$this->viewName.phtml"));
    }

    /**
     * Returns the source-file path of this view.
     * 
     * @return File The path of the view. Can be converted to string by using it.
     */
    public function getViewPath(): File
    {
        $path = $this->getControllerName() . "/" . $this->getViewName();
        return new File("App/Views/$path.phtml");
    }

    /**
     * Returns the current assigned controller to execute this view.
     * Views cannot be created if not explicitly being called from a controller.
     * 
     * @return string The associated controller name.
     */
    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    /**
     * Returns the name of this view.
     * 
     * @return string The name of this view.
     */
    public function getViewName(): string
    {
        return $this->viewName;
    }
}
