<?php

namespace Cosmic\Core\Bootstrap;

/**
 * This class represents a dependency that can be injected into a instance.
 */
class Dependency
{
    /**
     * A primitive dependency is just a key with a value. Value either being a string or an array.
     */
    const PRIMITIVE   = 0;

    /**
     * A singleton dependency binds a instance to a specific key. When requested again, the same instance will be returned.
     */
    const SINGLETON   = 1;

    /**
     * A contextual dependency creates a new instance of this dependency when requested.
     */
    const CONTEXTUAL  = 2;

    /**
     * @var int $dependencyType The kind of dependency.
     */
    private $dependencyType;


    /**
     * @var mixed $injectedValue Can be anything. The injected value.
     */
    private $injectedValue;

    /**
     * @var string[] $parameters The list of parameters that should be used to create the instance.
     */
    private array $parameters;

    /**
     * @var mixed $lazyInstance Holds the current instance of the dependency. Will be created only if needed.
     */
    private $lazyInstance;

    /**
     * @return mixed Return the dependency. Can be either a primitive or an instance(contextual or singleton).
     */
    public function &get()
    {
        if ($this->dependencyType == self::PRIMITIVE) {

            return $this->injectedValue;

        } else if ($this->dependencyType == self::SINGLETON) {

            if ($this->lazyInstance != null) {
                return $this->lazyInstance;
            }

            if ($this->requireAutowire($this->injectedValue)) {

                $this->lazyInstance = app()->create($this->injectedValue);

            } else {

                $dependency = $this->injectedValue;

                $this->lazyInstance = new $dependency(...$this->parameters);
            }

            return $this->lazyInstance;

        } else if ($this->dependencyType == self::CONTEXTUAL) {

            if ($this->requireAutowire($this->injectedValue)) {

                return app()->create($this->injectedValue);

            } else {

                $dependency = $this->injectedValue;
                return new $dependency(...$this->parameters);
            }
        }

        return null;
    }

    /**
     * Constructor. Create a new dependency with the given type and arguments.
     * 
     * @param mixed $dependencyType The kind of dependency.
     * @param mixed $injectedValue The value to store in the dependency.
     * @param array $parameters The arguments used to build the dependency.
     */
    public function __construct($dependencyType, $injectedValue, array $parameters = [])
    {
        $this->dependencyType = $dependencyType;
        $this->injectedValue = $injectedValue;
        $this->parameters = $parameters;
    }

    /**
     * Check if the dependency requires autowire, if not, try to use the arguments.
     * 
     * @param string $className The class to check.
     * 
     * @return bool True if the class requires autowire, false otherwise.
     */
    public function requireAutowire($className)
    {
        $reflectionClass = new \ReflectionClass($className);

        $constructor = $reflectionClass->getConstructor();

        if ($constructor != null) {

            $numberOfParameters = $constructor->getNumberOfParameters();
            $parameterMatch = (count($this->parameters) == $numberOfParameters) ? true : false;

            if (!$parameterMatch && $this->parameters == []) {
                return true;
            }
        }

        return false;
    }
}
