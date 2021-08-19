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
}




/*

getFullQualifiedParentClassName
get_class(object $object = ?) to get name from instance variable

getParentClassName


getFullQualifiedClassName
get_class(object $object = ?) to get name from instance variable

getClassName



    public static function hasTrait(string $class_name, string $trait_filter): bool
    {
        $traits = class_uses($class_name);
        return isset($traits[$trait_filter]);
    }