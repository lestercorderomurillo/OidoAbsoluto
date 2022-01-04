<?php

namespace Cosmic\Binder;

use Cosmic\Binder\Exceptions\CompileException;
use Cosmic\Utilities\Text;
use Cosmic\Utilities\Transport;

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
     * @var array $fixedParameters The parameters that will be used to render the element. 
     * They should be inmutable once created.
     */
    private array $fixedParameters;

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
        $this->fixedParameters = $parameters;
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
     * Return the stored properties for this element.
     * 
     * @return array The properties array.
     */
    public function getParameters(): array
    {
        return $this->fixedParameters;
    }

    /**
     * Return a new instance created with the given properties
     * 
     * @return Component The template component already created.
     */
    public function getComponentInstance(): Component
    {
        $componentClassName = app()->get(DOM::class)->getComponentClassName($this->componentName);

        $parameters = [];

        $reflectionClass = new \ReflectionClass($componentClassName);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor != null) {

            foreach ($constructor->getParameters() as $parameter) {

                if (isset($this->fixedParameters[$parameter->getName()])) {

                    $parameterCompiled = $this->fixedParameters[$parameter->getName()];

                    if (Text::startsWith($parameterCompiled, "@_ARRAY_")) {
                        $parameterCompiled = Transport::stringToArray($parameterCompiled);
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

        return new $componentClassName(...$parameters);
    }
}
