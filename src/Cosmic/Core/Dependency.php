<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core;

use Cosmic\Utilities\Strings;

/**
 * This class represents a dependency. By definition, a dependency is either a primitive, a singleton object, or a class name for instantiation.
 * When managing instances, dependencies will try to be resolved using the lazy pattern.
 */
class Dependency
{
    /**
     * A primitive dependency is just a key with a value. Value must be passed from injection.
     */
    const PRIMITIVE   = 0;

    /**
     * A singleton dependency binds a instance to a specific key. The first time the dependency is request,
     * a new instance will be created using the definition. When requested again, the same instance will be returned.
     */
    const SINGLETON   = 1;

    /**
     * A contextual dependency creates a new instance of this dependency when requested for all calls.
     */
    const CONTEXTUAL  = 2;

    /**
     * @var int $scope The scope of dependency.
     */
    private $scope;

    /**
     * @var mixed $injected Can be anything. The injected value.
     */
    private $injected;

    /**
     * @var string[] $parameters The list of parameters that should be used to create the instance.
     */
    private array $parameters;

    /**
     * @var mixed $lazyInstance Holds the current instance of the dependency. Will be created only if needed.
     */
    private $lazyInstance;

    /**
     * @var string[] $aliases A list of aliases for matching.
     */
    private $aliases;

    /**
     * Constructor. Create a new dependency with the given type and arguments.
     * 
     * @param int $scope The scope lifecycle for this dependency.
     * @param string $alias Alias.
     * @param mixed $injected The value to store in the dependency.
     * @param array $parameters The arguments used to build the dependency.
     */
    public function __construct(int $scope, string $alias, $injected, array $parameters = [])
    {
        $this->scope = $scope;
        $this->injected = $injected;

        $this->aliases[] = $alias;
        $this->aliases[] = Strings::getClassBaseName($alias);

        if($scope == static::SINGLETON || $scope == static::CONTEXTUAL){

            $reflectionClass = new \ReflectionClass($alias);
            $parentClass = $reflectionClass->getParentClass();

            if($parentClass !== false){
                $this->aliases[] = $parentClass->getName();
                $this->aliases[] = Strings::getClassBaseName($parentClass->getName());
            }
        }

        $this->parameters = $parameters;
    }


    /**
     * Return the dependency, using autowire to resolve all parameters when not given explicitly.
     * 
     * @return mixed The dependency. Can be either a primitive or an instance(contextual or singleton).
     */
    public function &get()
    {
        if ($this->scope == self::PRIMITIVE) {

            return $this->injected;

        } else if ($this->scope == self::SINGLETON) {

            if ($this->lazyInstance == null) {
                $this->lazyInstance = create($this->injected, ...$this->parameters); //new $this->injected(...$this->parameters);//instance($this->injected, ...$this->parameters);
            }

            return $this->lazyInstance;

        } else if ($this->scope == self::CONTEXTUAL) {

            //return instance($this->injected, ...$this->parameters);

        }

        return null;
        
    }

    /**
     * Check if the dependency requires autowire, if not, try to use the arguments.
     * 
     * @param string $requestDependency The class to compare.
     * @return bool True when this dependency can provide for the request.
     */
    public function match($requestDependency): bool
    {
        foreach($this->aliases as $alias){
            if($alias === $requestDependency){
                return true;
            }
        }

        return false;
    }
}
