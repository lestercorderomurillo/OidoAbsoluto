<?php

namespace Pipeline\Traits;

use Pipeline\Security\Cryptography;

trait ClassAwareTrait
{
    private \ReflectionClass $reflection_class = new \ReflectionClass($this);

    public function getConstant(string $const)
    {
        return $this->reflection_class->getConstant($const);
    }

    public function getProperties(int $visibility): array
    {
        $attributes = [];
        foreach ($this->reflection_class->getProperties($visibility) as $property) {
            $value = Cryptography::sanitizeString($this->{$property->getName()});
            $attributes[$property->getName()] = $value;
        }
        return $attributes;
    }

    public function getPublicProperties(): array
    {
        return $this->getProperties(\ReflectionProperty::IS_PUBLIC);
    }

    public function getPrivateProperties(): array
    {
        return $this->getProperties(\ReflectionProperty::IS_PRIVATE);
    }

    public function getProtectedProperties(): array
    {
        return $this->getProperties(\ReflectionProperty::IS_PROTECTED);
    }

    public function hasTrait(string $trait, $class_or_object): bool
    {
        if ($class_or_object == null) {
            $class_or_object = $this;
        }
        $traits = class_uses($class_or_object);
        return isset($traits[$trait]);
    }

    public function getFullyQualifiedClassName($object = null): string
    {
        if ($object == null) {
            $object = $this;
        }
        return get_class($this);
    }

    public function getClassName($object = null): string
    {
        return ((new \ReflectionClass($object)))->getShortName();
    }
}
