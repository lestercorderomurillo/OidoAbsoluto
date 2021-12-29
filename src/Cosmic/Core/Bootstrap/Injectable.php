<?php

namespace Cosmic\Core\Bootstrap;

use Cosmic\Core\Interfaces\ContainerInterface;
use Cosmic\Core\Exceptions\DependencyException;
use Cosmic\Core\Exceptions\DuplicatedKeyException;
use Cosmic\Core\Exceptions\NotFoundDependencyException;

/**
 * This class represents a injectable container that can resolve dependencies to other instances.
 */
class Injectable implements ContainerInterface
{
    /**
     * @var Dependency[] $dependencies A dictionary of dependencies already injected.
     */
    private array $dependencies = [];

    /**
     * Return the complete list of dependencies as an array.
     * 
     * @return Dependency[] The list of dependencies.
     */
    public function all(): array
    {
        return $this->dependencies;
    }

    /**
     * Clears all registered dependencies.
     * 
     * @return void
     */
    public function clear(): void
    {
        $this->dependencies = [];
    }

    /**
     * Add the given dependency to the container.
     * 
     * @param Dependency $value The dependency to add.
     * @inheritdoc
     * 
     * @throws DuplicatedKeyException If a dependency with the same alias already exists.
     */
    public function add(string $key, $value): bool
    {
        if ($this->has($key)) {
            throw new DuplicatedKeyException("The key \"$key\" used for the given dependency already exists. Use another alias instead");
        }

        $this->dependencies[$key] = $value;
        return true;
    }

    /**
     * Check if the dependency has been already injected to this container.
     * 
     * @inheritdoc
     */
    public function has(string $key): bool
    {
        return isset($this->dependencies[$key]);
    }

    /**
     * Return the dependency that matches the given key.
     * 
     * @inheritdoc
     * @throws NotFoundDependencyException if the dependency does not exist.
     * 
     * @return mixed
     */
    public function &get(string $key)
    {
        if ($this->has($key)) {
            return $this->dependencies[$key]->get();
        }

        throw new NotFoundDependencyException("The requested dependency with alias: \"$key\" is not injected yet");
    }

    /**
     * Delete the dependency who matches the given key.
     * 
     * @inheritdoc
     */
    public function delete(string $key): bool
    {
        if ($this->has($key)) {
            unset($this->dependencies[$key]);
        }

        return false;
    }

    /**
     * Inject a primitive. Must be either a string, array or an object instance.
     * 
     * @param string $key The key to inject the primitive.
     * 
     * @return void
     */
    public function injectPrimitive(string $key, $primitive): void
    {
        $dependency = new Dependency(Dependency::PRIMITIVE, $primitive);
        $this->add($key, $dependency);
    }

    /**
     * Inject a singleton class. The first time the class is requested, a new instance will be created.
     * 
     * @param string $className The class to inject.
     * @param string $parameters [Optional] If left blank, then all parameters will be injected using autowire.
     * @param string $alias [Optional] If left blank, the alias will be the same as the class name.
     * 
     * @return void
     */
    public function injectSingleton(string $className, array $parameters = [], string $alias = ""): void
    {
        $alias = ($alias != "") ? $alias : $className;

        $dependency = new Dependency(Dependency::SINGLETON, $className, $parameters);
        $this->add($alias, $dependency);
    }

    /**
     * Inject a class. When the dependency is requested, a new instance will be created.
     * 
     * @param string $className The class to inject.
     * @param string $parameters [Optional] If left blank, then all parameters will be injected using autowire.
     * @param string $alias [Optional] If left blank, the alias will be the same as the class name.
     * 
     * @return void
     */
    public function injectClass(string $className, array $parameters = [], string $alias = ""): void
    {
        $alias = ($alias != "") ? $alias : $className;

        $dependency = new Dependency(Dependency::CONTEXTUAL, $className, $parameters);
        $this->add($alias, $dependency);
    }

    /**
     * Returns a new instance of the given class. Can be invoked using custom names.
     * If failed to autowire a new instance, null will be returned.
     * 
     * @param string $alias The alias to look for.
     * 
     * @return mixed|null
     */
    public function create(string $alias)
    {
        $dependencies = [];
        $class = new \ReflectionClass($alias);

        $constructor = $class->getConstructor();
        $parameters = $constructor->getParameters();

        foreach ($parameters as $parameter) {

            $type = $parameter->getType();
            $dependency = $this->get($type);

            if ($dependency == null) {
                return null;
            }
            $dependencies[] = $dependency;
        }

        return new $alias(...$dependencies);
    }
}
