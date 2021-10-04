<?php

namespace Pipeline\Traits;

use Pipeline\Utilities\StringHelper;

trait ClassAwareTrait
{
    private function defaultIfNeeded($class_or_object = null)
    {
        if ($class_or_object == null) {
            $class_or_object = $this;
        }
        return $class_or_object;
    }

    public function getConstant(string $const, $class_or_object = null)
    {

        $class_or_object = $this->defaultIfNeeded($class_or_object);
        $reflection_class = new \ReflectionClass($class_or_object);

        return $reflection_class->getConstant($const);
    }

    public function getProperties(int $visibility, $class_or_object = null): array
    {
        $attributes = [];

        $class_or_object = $this->defaultIfNeeded($class_or_object);
        $reflection_class = new \ReflectionClass($class_or_object);

        foreach ($reflection_class->getProperties($visibility) as $property) {
            $value = StringHelper::sanitizeString($this->{$property->getName()});
            $attributes[$property->getName()] = $value;
        }
        return $attributes;
    }

    public function getPublicProperties($class_or_object = null): array
    {
        return $this->getProperties(\ReflectionProperty::IS_PUBLIC, $class_or_object);
    }

    public function getPrivateProperties($class_or_object = null): array
    {
        return $this->getProperties(\ReflectionProperty::IS_PRIVATE, $class_or_object);
    }

    public function getProtectedProperties($class_or_object = null): array
    {
        return $this->getProperties(\ReflectionProperty::IS_PROTECTED, $class_or_object);
    }

    public function hasTrait(string $trait, $class_or_object = null): bool
    {
        $class_or_object = $this->defaultIfNeeded($class_or_object);
        $traits = class_uses($class_or_object);
        return isset($traits[$trait]);
    }

    public function getFullyQualifiedClassName($class_or_object = null): string
    {
        $class_or_object = $this->defaultIfNeeded($class_or_object);
        return get_class($this);
    }

    public function getClassName($class_or_object = null): string
    {
        $class_or_object = $this->defaultIfNeeded($class_or_object);
        return ((new \ReflectionClass($class_or_object)))->getShortName();
    }
}
