<?php

namespace Cosmic\Binder;

use Cosmic\HTTP\Server\Router;
use Cosmic\Utilities\Collection;
use Cosmic\Utilities\Text;
use Cosmic\Core\Exceptions\NotFoundDependencyException;
use Cosmic\Binder\Exceptions\NotFoundComponentException;
use Cosmic\Utilities\HTML;

/**
 * This class represents the cosmic Binder tree.
 */
class DOM
{
    /**
     * @var string[] $functions Holds all the javascripts functions from all components.
     */
    private array $functions;

    /**
     * @var string[] $initialStates Holds all the initial stateful javascripts functions from all components.
     */
    private array $initialStates;

    /**
     * Constructor. Expected be resolved using dependency injection.
     * 
     * @param Compiler $compiler The compiler instance.
     * @param Router $router The router instance.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->functions = [];
        $this->initialStates = [];
    }

    /**
     * Return the full class namespace from the DOM tag name.
     * Thanks to this, elements can later on create instances of this component.
     * 
     * @param string $tag The dom tag to get the namespace from.
     * 
     * @return string The full class name of the component.
     * @throws NotFoundComponentException On error.
     */
    public function getComponentClassName(string $tag): string
    {
        try {
            return app()->get(Component::getPublishNameFromTag($tag));
        } catch (NotFoundDependencyException $exception) {
            throw new NotFoundComponentException("Binder doesn't have the component \"$tag\" registered yet. \nFrom Internal: " . $exception->getMessage());
        }
    }

    /**
     * Register the component class in the container. 
     * 
     * @param string $className The component class to inject into the container.
     * 
     * @return void
     */
    public function registerComponent(string $className): void
    {
        app()->injectPrimitive(Component::getPublishName($className), $className);
    }

    /**
     * Register the given script for later client side export.
     * 
     * @param string $compiledJavascript The compiled javascript code.
     * 
     * @return void
     */
    public function registerJavascriptSourceCode(string $compiledJavascript): void
    {
        $value = trim($compiledJavascript);

        if ($value != __EMPTY__) {
            $this->functions[] = $value;
        }
    }

    /**
     * Register the given state script for later client side export.
     * 
     * @param string $compiledJavascript The compiled javascript code.
     * 
     * @return void
     */
    public function registerInitialStateSourceCode(string $compiledJavascript): void
    {
        $value = trim($compiledJavascript);

        if ($value != __EMPTY__) {
            $this->initialStates[] = $value;
        }
    }

    /**
     * Return the resulting compiled scripts as one single javascript string that be exported 
     * later on the view. This method merge all scripts from all components.
     *
     * @return string The output javascript source code.
     */
    public function getOuputJavascript(): string
    {
        $initialOutput = HTML::encodeInJScript(trim("state[\"_globalComponent_\"] = {};\n" . implode("\n", $this->initialStates)));
        $output = HTML::encodeInJScript(trim(implode("\n", $this->functions)), false);

        return "\n" . $initialOutput . "\n" . $output;
    }

    /**
     * Return a collection of all files required to compile for components. 
     * 
     * @return File[] A collection of files. 
     */
    public function getSCSSFiles(): array
    {
        $all = app()->all();
        $files = [];

        foreach ($all as $key => $value) {
            if (Text::startsWith($key, "Component@")) {
                $value = $value->get();
                $files = Collection::mergeList($files, Component::getStyleFilesPath($value));
            }
        }

        return $files;
    }
}
