<?php

namespace Cosmic\Traits;

use Cosmic\Utilities\Text;

/**
 * This trait provides classes with methods for dealing with reflection in a simpler way.
 * Classes then will become "aware" of their class and methods definitions.
 */
trait ClassAwareTrait
{
    /**
     * Get the reflection method for this constructor.
     * 
     * @return \ReflectionMethod The constructor method.
     */
    public function getConstructor()
    {
        $reflectionClass = new \ReflectionClass($this);
        return $reflectionClass->getConstructor();
    }

    /**
     * Get the value of a constant for this class (extern form).
     * 
     * @param string $constant The constant to check for.
     * @param string $className If not null, this class  will be used in the reflection call.
     * 
     * @return mixed|false The constant value, or false if the constant is not found.
     */
    public static function getClassConstant(string $constant, $className)
    {
        $reflectionClass = new \ReflectionClass($className);
        return $reflectionClass->getConstant($constant);
    }

    /**
     * Get the value of a constant for this class.
     * 
     * @param string $constant The constant to check for.
     * 
     * @return mixed|false The constant value, or false if the constant is not found.
     */
    public function getConstant(string $constant)
    {
        $reflectionClass = new \ReflectionClass($this);
        return $reflectionClass->getConstant($constant);
    }

    /**
     * Get a collection of methods for this class.
     * 
     * @return \ReflectionMethod[] The collection of all methods.
     */
    public function getMethods()
    {
        $reflectionClass = new \ReflectionClass($this);
        return $reflectionClass->getMethods();
    }

    /**
     * Get all the properties of this current class.
     * 
     * @param int $visibility The visibility of the properties to search for.
     * 
     * @return array A collection with all the properties.
     */
    public function getProperties(int $visibility): array
    {
        $attributes = [];

        $reflectionClass = new \ReflectionClass($this);

        foreach ($reflectionClass->getProperties($visibility) as $property) {
            $value = Text::sanitizeString($this->{$property->getName()});
            $attributes[$property->getName()] = $value;
        }
        return $attributes;
    }

    /**
     * Get the public properties of this current class.
     * 
     * @return array A collection with all the public properties.
     */
    public function getPublicProperties(): array
    {
        return $this->getProperties(\ReflectionProperty::IS_PUBLIC);
    }

    /**
     * Get the private properties of this current class.
     * 
     * @return array A collection with all the private properties.
     */
    public function getPrivateProperties(): array
    {
        return $this->getProperties(\ReflectionProperty::IS_PRIVATE, $this);
    }

    /**
     * Get the protected properties of this current class.
     * 
     * @return array A collection with all the protected properties.
     */
    public function getProtectedProperties(): array
    {
        return $this->getProperties(\ReflectionProperty::IS_PROTECTED, $this);
    }

    /**
     * Check if the current class object contains the given trait.
     * 
     * @param string $trait The trait to check.
     * 
     * @return bool True if class has the given trait, false otherwise.
     */
    public function hasTrait(string $trait): bool
    {
        $traits = class_uses($this);
        return isset($traits[$trait]);
    }

    /**
     * Return the class name for instantiation.
     *
     * @return string The class name.
     */
    public function getClassName(): string
    {
        return static::class;
    }

}
