<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Abstracts;

use Cosmic\Core\Dependency;
use Cosmic\Core\Interfaces\RunnableInterface;
use Cosmic\Core\Providers\ConfigurationProvider;
use Cosmic\Core\Providers\ErrorHandlingProvider;
use Cosmic\Core\Providers\LoggerProvider;
use Cosmic\FileSystem\Providers\FileSystemProvider;

/**
 * This class represents a cosmic application.
 */
abstract class Application extends Booteable implements RunnableInterface
{
    private static $application;

    protected DIContainer $container;

    /**
     * 
     */
    public static function app(string $key = '')
    {
        if ($key == '') {
            return static::$application;
        }

        return static::$application->container->get($key);
    }

    /**
     * Bind a new instance to the framework.
     */
    public static function bind(Application $application): int
    {
        static::$application = $application;
        static::$application->boot();
        return static::$application->run();
    }

    /**
     * @inheritdoc
     */
    public function boot(): void
    {
        $this->container = (!isset($this->container)) ? new DIContainer() : $this->container;

        FileSystemProvider::default();
        ConfigurationProvider::default();
        LoggerProvider::default();
        ErrorHandlingProvider::default();
    }

    /**
     * @inheritdoc
     */
    public function dispose(): void
    {
    }

    /**
     * Returns a new instance of the given class. Can be invoked using custom names.
     * If failed to autowire a new instance, null will be returned instead.
     * 
     * @param string|callable $callback A callback function.
     * @return mixed|null
     */
    public function create($callback, ...$arguments)
    {
        $parameters = [];
        $outputParameters = [];
        $namedParameters = (count($arguments) == 1);

        if (is_string($callback)) {

            $reflectionClass = new \ReflectionClass($callback);
            $reflectionConstructor = $reflectionClass->getConstructor();

            if ($reflectionConstructor != null) {
                $parameters = $reflectionConstructor->getParameters();
            }

            $isClosure = false;

        } else if (is_callable($callback)) {

            $reflectionClosure = new \ReflectionFunction($callback);
            $parameters = $reflectionClosure->getParameters();

            $isClosure = true;
        }

        $index = 0;
        foreach ($parameters as $parameter) {

            if ($namedParameters && isset($arguments[0][$parameter->getName()])) {
                $outputParameters[] = $arguments[0][$parameter->getName()];
            }else if (!$namedParameters && isset($arguments[$index])) {
                $outputParameters[] = $arguments[$index];
            }else if ($this->container->has(getNativeType($parameter))) {
                $outputParameters[] = $this->container->get(getNativeType($parameter));
            } else if ($parameter->isOptional()) {
                $outputParameters[] = $parameter->getDefaultValue();
            } else {
                throw new \RuntimeException("Cannot inject the closure. Expected parameter '$parameter'");
            }

            $index++;
        }

        if ($isClosure) {
            return $callback(...$outputParameters);
        } else {
            return new $callback(...$outputParameters);
        }

        if (count($outputParameters) !== count($parameters)) {
        }


        /*if ($isClosure) {

            return $closure(...$outputParameters);

        } else {

            return new $className(...$outputParameters);

        }*/

        /*
        
        $reflectionMethod = new \ReflectionMethod($alias);

        $reflectionClass = new \ReflectionClass($alias);

        $constructor = $reflectionClass->getConstructor();*/



        /*$parameters = $constructor->getParameters();

        if ($parameters == []) {
            return new $alias();
        }

        $outputParameters = [];

        if ($namedParameters) {

            $dictionary = $arguments[0];

            foreach ($parameters as $parameter) {

                if (isset($dictionary[$parameter->getName()])) {
                    $outputParameters[] = $dictionary[$parameter->getName()];
                } else if ($this->container->has(getNativeType($parameter))) {
                    $outputParameters[] = $this->container->get(getNativeType($parameter));
                } else if ($parameter->isOptional()) {
                    $outputParameters[] = $parameter->getDefaultValue();
                }
            }
        } else {

            $index = 0;
            foreach ($parameters as $parameter) {

                if (isset($arguments[$index])) {

                    $outputParameters[] = $arguments[$index];
                } else if ($this->container->has(getNativeType($parameter))) {

                    $outputParameters[] = $this->container->get(getNativeType($parameter));
                } else if ($parameter->isOptional()) {

                    $outputParameters[] = $parameter->getDefaultValue();
                }

                $index++;
            }
        }

        if (count($outputParameters) !== count($parameters)) {
            throw new \RuntimeException("nope");
        }

        return new $alias(...$outputParameters);*/
    }


    /**
     * Inject a primitive. Must be either a string, array or an object instance.
     * 
     * @param string $key The key to inject the primitive.
     * 
     * @return void
     */
    public function primitive(string $key, $primitive): void
    {
        $this->container->add($key, new Dependency(Dependency::PRIMITIVE, $key, $primitive));
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
    public function singleton(string $className, array $parameters = [], string $alias = ""): void
    {
        $alias = ($alias != "") ? $alias : $className;
        $this->container->add($alias, new Dependency(Dependency::SINGLETON, $alias, $className, $parameters));
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
    public function class(string $className, array $parameters = [], string $alias = ""): void
    {
        $alias = ($alias != "") ? $alias : $className;
        $this->container->add($alias, new Dependency(Dependency::CONTEXTUAL, $alias, $className, $parameters));
    }
}
