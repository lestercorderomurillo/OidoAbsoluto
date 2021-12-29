<?php

namespace Cosmic\Binder;

use Cosmic\Utilities\Text;
use Cosmic\Utilities\Collection;
use Cosmic\Traits\ValuesSetterTrait;
use Cosmic\Traits\ClassAwareTrait;
use Cosmic\Binder\Exceptions\InvalidComponentException;
use Cosmic\FileSystem\Paths\File;

use function Cosmic\Core\Bootstrap\app;
use function Cosmic\Core\Bootstrap\guid;

/**
 * This class represents a cosmic component. Should be extended to create new components.
 */
abstract class Component
{
    use ClassAwareTrait;
    use ValuesSetterTrait;

    /**
     * @var string $key The component key. Needed for DOM transforming.
     */
    private string $key;

    /**
     * Return the publish component name for injectable container's.
     *
     * @param string className The complete namespaced class name.
     * 
     * @return string The component name.
     */
    public static function getPublishName($className): string
    {
        $reflectionClass = new \ReflectionClass($className);
        if ($reflectionClass->getConstant("Name") !== false) {
            return "Component@" . $reflectionClass->getConstant("Name");
        }
        return "Component@" . Text::getNamespaceBaseName($className);
    }

    /**
     * Return the publish component name from the HTML tag name.
     *
     * @return string $tag The HTML tag name.
     * 
     * @return string The component name.
     */
    public static function getPublishNameFromTag($tag): string
    {
        return "Component@" . $tag;
    }

    /**
     * Return the list of scss files that this component uses.
     * 
     * @param string $className The component name to check.
     * 
     * @return File[] A list of files.
     */
    public static function getStyleFilesPath($className): array
    {
        $paths = [];

        $constant = self::getClassConstant("Styles", $className);

        if ($constant !== false) {
            foreach ($constant as $line) {
                $paths[] = new File("src/Cosmic/Bundle/Components/$line");
            }
        }

        return $paths;
    }

    /**
     * Return the global DOM tree compiler.
     *
     * @return Compiler The DOM tree.
     */
    public function &getCompiler(): Compiler
    {
        return app()->get(Compiler::class);
    }

    /**
     * Return the current body content (including all Cosmic components childs nodes)
     * Childs expressions now can access the tokens from this component.
     * 
     * @var array $passthroughTokens The HTML result.
     * 
     * @return string The HTML result.
     */
    public function renderChilds(array $passthroughTokens): string
    {
        if (isset($this->body)) {

            $passthroughTokens = Collection::mapKeys($passthroughTokens, function (string $key) {
                return "parent." . $key;
            });

            return $this->getCompiler()->compileString($this->body, $passthroughTokens, 1) . "\n";
        }
        return "";
    }

    /**
     * Return the component class name for instantiation.
     *
     * @return string The class name.
     */
    public function getClassName(): string
    {
        return static::class;
    }

    /**
     * Set the new component internal key to a new random value, to help cosmic keep track of this component when trying to perform server side rendering.
     *
     * @return void
     */
    public function resetKey(): void
    {
        $this->key = guid();
    }

    /**
     * Set the current component internal key to the given value.
     * 
     * @param string $key The key to bind to this component.
     *
     * @return void
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * Return the binded key.
     *
     * @return string|null
     */
    public function getKey()
    {
        return isset($this->key) ? $this->key : null;
    }

    /**
     * Return the render renderTemplate for this component.
     * If not present, this will throw an exception
     *
     * @return string A HTML Cosmic renderTemplate string.
     * @throws InvalidComponentException
     */
    public function getRenderTemplate(): string
    {
        if (method_exists($this, 'render')) {

            $this->renderTemplate = call_user_func([$this, 'render']);
            return $this->renderTemplate;
        }

        throw new InvalidComponentException("This component is missing a render method");
    }

    /**
     * Return the name of all of the required parameters for this component.
     *
     * @return string[] A collection of all required keys.
     */
    public function getRequiredParameters()
    {
        $constructor = $this->getConstructor();
        $parameters = $constructor->getParameters();

        $requiredParameters = [];

        foreach ($parameters as $parameter) {
            if (!$parameter->isOptional()) {
                $requiredParameters[] = $parameter->getName();
            }
        }
        return $requiredParameters;
    }

    /**
     * Return an dictionary containing all keys with their respective values.
     *
     * @return string[] A collection of all keys with their respective values.
     */
    public function getDefaultParameters()
    {
        $constructor = $this->getConstructor();
        $parameters = $constructor->getParameters();

        $defaultParameters = [];

        foreach ($parameters as $parameter) {
            if ($parameter->isOptional()) {
                $defaultParameters[$parameter->getName()] = $parameter->getDefaultValue();
            }
        }
        return $defaultParameters;
    }

    /**
     * Return a collection of callbacks for this component.
     *
     * @return Callback[] An list of callbacks. 
     */
    public function getCallbacks(): array
    {
        $componentMethods = $this->getMethods();
        $callbacks = [];

        foreach ($componentMethods as $method) {
            $actionName = $method->getName();

            if ($method->isPublic() && $method->class == $this->getClassName() && !in_array($actionName, ["__construct", "render"])) {

                $callbacks[] = new Callback(self::getPublishName($this), $actionName, $this->getKey(), $method->getParameters());
            }
        }

        return $callbacks;
    }

    /**
     * @return bool Return true if this component doesn't require a closing tag.
     */
    public function isInlineComponent(): bool
    {
        if ($this->getConstant('Inline') !== false) {
            return $this->getConstant('Inline');
        }
        return false;
    }
}
