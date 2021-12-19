<?php

namespace Cosmic\Core\Boot;

use Cosmic\Core\Exceptions\UnavailableDependencyException;

/**
 * This class represents a dependency container. 
 * These dependencies can be injected automatically when using the create() method.
 */
abstract class DependencyContainer
{
    /**
     * @var array $dependencies An array storing the dependencies.
     */
    private static array $dependencies = [];

    /**
     * Clears all registered dependencies.
     * 
     * @return void
     */
    public static function clear(): void
    {
        self::$dependencies = [];
    }

    /**
     * Returns a dependency. The instance takes in account registered lifetime.
     * 
     * @param string $id The name used to register the dependency.
     * 
     * @throws UnavailableDependencyException
     * @return mixed|null
     */
    public static function get(string $id)
    {
        if (isset(self::$dependencies[$id])) {
            if (self::$dependencies[$id]["lifetime"] == Lifetime::ContextLifetime) {
                $class = self::$dependencies[$id]["class"];
                return new $class(...self::$dependencies[$id]["parameters"]);
            } else if (self::$dependencies[$id]["lifetime"] == Lifetime::RequestLifetime) {
                return self::$dependencies[$id]["instance"];
            } else if (self::$dependencies[$id]["lifetime"] == Lifetime::SerializableLifetime) {
                return "Unimplemented";
            }
        }

        throw new UnavailableDependencyException("The requested dependency is not injected yet.", 500);
    }

    /**
     * Inject a new dependency into the container.
     * 
     * @param Lifetime $lifetime The lifetime of the dependency.
     * @param string $class The particular class to inject.
     * @param mixed[] $parameters The parameters to build the instance. 
     * Must be provided in the same order as the parameters in the constructor.
     * 
     * @param string $id Set a custom name for the dependency. If not provided, defaults to class name.
     * This is very useful when using multiple instances of the same class but with different properties.
     * 
     * @return void
     */
    public static function inject(int $lifetime, string $class, array $parameters = [], string $id = null): void
    {
        $id = ($id == null) ? $class : $id;

        if (!isset(self::$dependencies[$id])) {
            self::$dependencies[$id] = [];
        }

        self::$dependencies[$id]["lifetime"] = $lifetime;
        self::$dependencies[$id]["class"] = $class;
        self::$dependencies[$id]["parameters"] = $parameters;

        if ($lifetime == Lifetime::ContextLifetime) {
            self::$dependencies[$id]["instance"] = null;
        } else if ($lifetime == Lifetime::RequestLifetime) {
            self::$dependencies[$id]["instance"] = new $class(...$parameters);
        }
    }

    /**
     * Returns a new instance of the given class. Can be invoked using custom names.
     * If failed to autowire a new instance, null will be returned.
     * 
     * @param static $dependencyName  The class or custom name used when injected previously.
     * 
     * @return mixed|null
     */
    public static function create(string $dependencyName)
    {
        $dependencies = [];
        $class = new \ReflectionClass($dependencyName);

        $constructor = $class->getConstructor();
        $parameters = $constructor->getParameters();

        foreach ($parameters as $parameter) {

            $type = $parameter->getType();
            $dependency = self::get($type);

            if ($dependency == null) {
                return null;
            }
            $dependencies[] = $dependency;
        }

        return new $dependencyName(...$dependencies);
    }
}
