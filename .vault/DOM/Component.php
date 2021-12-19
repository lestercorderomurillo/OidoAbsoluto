<?php

namespace Cosmic\DOM;

use Cosmic\DOM\Exceptions\InvalidComponentException;

class Component
{
    private string $template;
    private string $callbacks;
    private array $required_parameters;
    private array $default_parameters;

    /**
     * Returns the component name.
     *
     * @return string
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * Returns all javascripts functions defined in the callbacks() function.
     * If not present, this function will return null.
     * Adds 'componentName' to each function to avoid conflicts in namespaces.
     *
     * @return string|null
     */
    public function getCallbacks(): ?string
    {
        if ($this->callbacks) return $this->callbacks;

        if (method_exists($this, 'callbacks')) {
            $callbacks = call_user_func([$this, 'callbacks']);
            $name = $this->getComponentName();
            if (strlen($callbacks) > 0) {
                $callbacks = preg_replace("/(?<!.)function (\w*)\(/", "function $name" . "_$4(__scope__, ", $callbacks);
                $this->callbacks = preg_replace("/(set|get)State\(/", "$0__scope__, ", $callbacks);
                return $this->callbacks;
            }
        }

        return null;
    }

    /**
     * Returns the render template for this component.
     * If not present, this will throw an exception
     *
     * @return string
     */
    public function getRenderTemplate(): string
    {
        if ($this->template) return $this->template;

        if (method_exists($this, 'render')) {

            $this->template = call_user_func([$this, 'render']);
            if (strlen($this->template) > 0) {
                return $this->template;
            }
        }

        throw new InvalidComponentException("This component is missing a render method.");
    }

    /**
     * Returns the name of all of the required parameters for this component.
     *
     * @return string[]
     */
    public function getRequiredParameters()
    {
        if ($this->required_parameters) return $this->required_parameters;

        $reflect = new \ReflectionClass($this);
        $parameters = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        $this->required_parameters = [];

        foreach ($parameters as $parameter) {
            if(!$parameter->hasDefaultValue()){
                $this->required_parameters[] = $parameter->getName();
            }
        }
        return $this->required_parameters;
    }

    /**
     * Returns an dictionary containing all keys with their respective values.
     *
     * @return string[]
     */
    public function getDefaultParameters()
    {
        if ($this->default_parameters) return $this->default_parameters;

        $reflect = new \ReflectionClass($this);
        $parameters = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        $this->default_parameters = [];

        foreach ($parameters as $parameter) {
            if($parameter->hasDefaultValue()){
                $this->default_parameters[$parameter->getName()] = $parameter->getDefaultValue();
            }
        }
        return $this->default_parameters;
    }
}
