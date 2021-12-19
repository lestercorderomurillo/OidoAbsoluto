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

    /*public function getFullyQualifiedClassName($class_or_object = null): string
    {
        $class_or_object = $this->defaultIfNeeded($class_or_object);
        return get_class($this);
    }

    public function getClassName($class_or_object = null): string
    {
        $class_or_object = $this->defaultIfNeeded($class_or_object);
        return ((new \ReflectionClass($class_or_object)))->getShortName();
    }*/

    /*private function defaultIfNeeded($class_or_object = null)
    {
        if ($class_or_object == null) {
            $class_or_object = $this;
        }
        return $class_or_object;
    }*/

}
