<?php

namespace VIP\Model;

use VIP\Core\BaseObject;
use VIP\Factory\ResponseFactory;
use VIP\Utilities\StringHelper;

abstract class AbstractModel extends BaseObject
{
    public function __construct()
    {
        $this->setObjectID(0);
    }

    public function getAttributesPlaceholders(): array
    {
        $attributes = [];
        $this->reflection_class = new \ReflectionClass($this);
        foreach ($this->reflection_class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $attributes[] = '`' . $property->getName() . '` = :' . $property->getName();
        }
        return $attributes;
    }

    public function getAttributesValues(): array
    {
        $attributes = [];
        $this->reflection_class = new \ReflectionClass($this);
        foreach ($this->reflection_class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $value = StringHelper::sanitizeString($this->{$property->getName()});
            $attributes[":" . $property->getName()] = $value;
        }
        return $attributes;
    }

    public function getTableName(): string
    {
        $this->reflection_class = new \ReflectionClass($this);
        $const = $this->reflection_class->getConstant("table");
        if ($const === false) {
            ResponseFactory::createError(500, "All models should have the table name const.");
        }
        return $const;
    }
}
