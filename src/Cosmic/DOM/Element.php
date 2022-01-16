<?php

namespace Cosmic\DOM;

use Cosmic\DOM\Exceptions\CompileException;

/**
 * This class represents a cosmic element. Similar to an HTML object but with server side rendering capability.
 */
class Element
{
    /**
     * @var string $componentName The component to use as the template.
     */
    private string $componentName;

    /**
     * @var string[] $parameters The parameters that will be used to render the element. 
     * They should be inmutable once created to avoid conflicts later on when doing stateful rendering.
     */
    private array $parameters;

    /**
     * @var string[] $events The delegated events for this element.
     */
    private array $events;

    /**
     * Constructor. Creates a new element using the given component as the template.
     * Once the parameters has been set, they cannot be changed anymore, but body content can.
     * 
     * @param string $componentClassName The component to use as the template.
     * @param array $parameters The parameters to use to create this element.
     * 
     * @return void
     */
    public function __construct(string $componentName, array $parameters)
    {
        $this->componentName = $componentName;
        $this->events = [];
        $this->parameters = [];

        foreach ($parameters as $key => $value){
            if (str_starts_with($key, "(") && str_ends_with($key, ")")){
                $this->events[$key] = $value;
            }else{
                $this->parameters[$key] = $value;
            }
        }

        if (!isset($this->parameters["id"])) {
            $this->parameters["id"] = generateID();
        }
    }

    /**
     * Assigns the key with the given value.
     * 
     * @param string $key The key to use.
     * @param string $value The value to assign.
     */
    public function setParameter(string $key, string $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Return the stored property for this element.
     * 
     * @param string $key The key to retrieve from.
     * 
     * @return array The properties array.
     */
    public function getParameter(string $key): string
    {
        return $this->parameters[$key];
    }

    /**
     * Store an event in the given key.
     * 
     * @param string $key The key to store in.
     * @param string $value The value to store.
     * @return void
     */
    public function setEvent(string $key, string $value): void
    {
        $this->events[$key] = $value;
    }

    /**
     * Return the stored event for this element.
     * 
     * @param string $key The key to retrieve from.
     * 
     * @return array The properties array.
     */
    public function getEvent(string $key): string
    {
        return $this->events[$key];
    }

    /**
     * Return the component name associated with this element.
     * 
     * @return string The component name.
     */
    public function getComponentName(): string
    {
        return $this->componentName;
    }

    /**
     * Return the component name associated with this element.
     * 
     * @return bool The component name.
     */
    public function isGenericElement(): bool
    {
        return preg_match('/^\p{Lu}/u', $this->componentName) ? false : true;
    }

    /**
     * Return the stored properties for this element.
     * 
     * @return array The properties array.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Return the stored events for this element.
     * 
     * @return array The properties array.
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Return a new instance created with the given properties
     * 
     * @return Component The template component already created.
     */
    public function getComponentInstance(): Component
    {
        $componentClassName = app()->get(Bindings::class)->getComponentClassName($this->componentName);

        $parameters = [];

        $reflectionClass = new \ReflectionClass($componentClassName);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor != null) {

            foreach ($constructor->getParameters() as $parameter) {

                if (isset($this->parameters[$parameter->getName()])) {

                    $parameterCompiled = $this->parameters[$parameter->getName()];

                    if (str_starts_with($parameterCompiled, "@ARR")) {
                        $parameterCompiled = Collections::decodeStringToArray($parameterCompiled);
                    }

                    $type = $parameter->getType();
                    
                    if ($type instanceof \ReflectionNamedType && $type->getName() == "int") {
                        $parameterCompiled = intval($parameterCompiled);
                    }

                    $parameters[] = $parameterCompiled;
                    
                } else if ($parameter->isDefaultValueAvailable()) {

                    $parameters[] = $parameter->getDefaultValue();

                } else {

                    throw new CompileException("Unable to render '" . $this->getComponentName() . "' component because $parameter is missing");
                }
            }
        }

        /** @var Component $component */
        $component = new $componentClassName(...$parameters);

        if(!isset($component->id)){
            $component->id = $this->parameters["id"];
        }

        if(!isset($component->class)){
            $component->class = (isset($this->parameters["class"])) ? $this->parameters["class"] : __EMPTY__;
        }

        $component->events = __EMPTY__;

        foreach ($this->events as $key => $event) {
            $component->events .= $key . '="' . $event . '" ';
        }

        $component->events = trim($component->events);

        app()->get(Bindings::class)->registerInitialStateSourceCode($component->getBaseStateJavascript());
        app()->get(Bindings::class)->registerJavascriptSourceCode($component->getCompiledJavascriptFunctions());

        return $component;
    }
}
