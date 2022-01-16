<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Abstracts;

use Cosmic\Core\Dependency;
use Cosmic\Core\Interfaces\ContainerInterface;
use Cosmic\Core\Exceptions\DuplicatedKeyException;
use Cosmic\Core\Exceptions\NotFoundDependencyException;

/**
 * This class represents a injectable container that can resolve dependencies to other instances.
 */
class DIContainer implements ContainerInterface
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
     * @throws DuplicatedKeyException If a dependency with the same alias already exists.
     * @inheritdoc
     */
    public function add($key, $value): bool
    {
        if ($this->has($key)) 
        {
            throw new DuplicatedKeyException("The key \"$key\" used for the given dependency already exists. Use another alias instead");
        }

        $this->dependencies[] = $value;
        return true;
    }

    /**
     * Check if the dependency has been already injected to this container.
     * 
     * @inheritdoc
     */
    public function has($key): bool
    {
        foreach($this->dependencies as $dependency){
            if($dependency->match($key)){
                return true;
            }
        }

        return false;
    }

    /**
     * Return the dependency that matches the given key.
     * 
     * @return mixed
     * @throws NotFoundDependencyException if the dependency does not exist.
     * @inheritdoc
     */
    public function &get($key)
    {
        foreach($this->dependencies as $dependency){
            if($dependency->match($key)){
                return $dependency->get();
            }
        }

        throw new NotFoundDependencyException("The requested dependency with alias: \"$key\" is not injected yet in the IoC Container");
    }

    /**
     * Delete the dependency who matches the given key.
     * 
     * @inheritdoc
     */
    public function delete($key): bool
    {
        $index = 0;
        foreach($this->dependencies as $dependency){

            if($dependency->match($key)){
                unset($dependency[$index]);
            }
            $index++;
        }

        return false;
    }
}
