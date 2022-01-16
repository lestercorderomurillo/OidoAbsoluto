<?php

namespace Cosmic\DOM;

use Cosmic\Utilities\Text;
use Cosmic\Utilities\Collection;
use Cosmic\Traits\ValuesSetterTrait;
use Cosmic\Traits\ClassAwareTrait;
use Cosmic\FileSystem\Paths\File;
use Cosmic\DOM\Exceptions\InvalidComponentException;
use Cosmic\FileSystem\FileSystem;

/**
 * This class represents a cosmic component. Should be extended to create new components.
 */
abstract class Component
{
    use ClassAwareTrait;
    use ValuesSetterTrait;

    /**
     * @var string $id The component ID. Needed for DOM manipulation and transforming.
     */
    public string $id;

    /**
     * @var string $events Store this component compiled delegated events.
     */
    public string $events;

    /**
     * @var string $class Store this component compiled delegated classes.
     */
    public string $class;

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
        return "Component@" . StrgetClassBaseName($className);
    }

    /**
     * Return the publish component name from the HTML tag name.
     *
     * @param string $tag The HTML tag name.
     * 
     * @return string The component name.
     */
    public static function getPublishNameFromTag(string $tag): string
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

                $path = new FilePath("src/Cosmic/Bundle/Components/$line");
                $pathInApp = new FilePath("app/Components/$line");

                if(FileSystem::exists($path)) {
                    $paths[] = $path;
                }else if (FileSystem::exists($pathInApp)) {
                    $paths[] = $pathInApp;
                }
            }
        }

        return $paths;
    }

    /**
     * Return the component name simplified.
     * 
     * @return string The component name simplified.
     */
    public function getSimplifiedName(): string
    {
        return StrgetClassBaseName($this->getClassName());
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

            $passthroughTokens = Collections::mapKeys($passthroughTokens, function (string $key) {
                return "parent." . $key;
            });

            return app()->get(Compiler::class)->compileString($this->body, $passthroughTokens, 1) . "\n";
        }

        return __EMPTY__;
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
     * Return the render renderTemplate for this component.
     * If not present, this will throw an exception
     *
     * @return string A HTML Cosmic renderTemplate string.
     * @throws InvalidComponentException
     */
    public function getRenderTemplate(): string
    {
        if (method_exists($this, 'render')) {

            $renderTemplate = call_user_func([$this, 'render']);

            if ($renderTemplate != null && strlen($renderTemplate) > 0) {
                return $renderTemplate;
            }

            return __EMPTY__;
        }

        throw new InvalidComponentException("This component is missing a render method in their class definition");
    }

    /**
     * Return the compiled initial state javascripts functions of this component.
     *
     * @return string The compiled javascript source code.
     */
    public function getBaseStateJavascript(): string
    {
        return trim("state[\"" . $this->id . "\"] = {};\n");
    }

    /**
     * Return the compiled javascripts functions of this component.
     *
     * @return string The compiled javascript source code.
     */
    public function getCompiledJavascriptFunctions(): string
    {
        $script = __EMPTY__;

        if (method_exists($this, 'scripts')) {
            $componentName = $this->getSimplifiedName() . "_" . $this->id . "_";
            $script = call_user_func([$this, 'scripts']);
            $script = preg_replace("/fn\.([A-z0-9_]+)/", "$componentName$1", $script);
            $script = preg_replace("/component\.([A-z0-9_]+)(?=\()/", "$componentName$1", $script);
            $script = preg_replace("/component\./", "state[\"" . $this->id . "\"].", $script);
            $script = preg_replace("/extern\./", "state[\"_globalComponent_\"].", $script);
            $script = preg_replace("/function (?=[A-z]+)/", "function $componentName", $script);
        }

        return trim($script);
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

    /**
     * Call the dispose method of this component if available.
     *
     * @return void
     */
    public function tryDispose(): void
    {
        if (method_exists($this, 'dispose')) {

            call_user_func([$this, 'dispose']);
        }
    }
}
