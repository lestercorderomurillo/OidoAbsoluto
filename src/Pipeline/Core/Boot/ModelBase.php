<?php

namespace Pipeline\Core\Boot;

use Pipeline\Core\Boot\NodeBase;
use Pipeline\Core\Exceptions\InvalidModelException;
use Pipeline\Traits\ClassAwareTrait;
use Pipeline\Traits\ValuesSetterTrait;

abstract class ModelBase extends NodeBase
{
    use ClassAwareTrait;
    use ValuesSetterTrait;

    public function __construct()
    {
        $this->setId(0);
    }

    public function getAttributesPlaceholders(): array
    {
        $attributes = [];
        foreach ($this->getPublicProperties() as $property => $value) {
            $attributes[] = '`' . $property . '` = :' . $property;
        }
        return $attributes;
    }

    public function getAttributesValues(): array
    {
        $attributes = [];
        foreach ($this->getPublicProperties() as $property => $value) {
            $attributes[":" . $property] = $value;
        }
        return $attributes;
    }

    public function getTableName(): string
    {
        $const = $this->getConstant("table");
        if ($const === false) {
            throw new InvalidModelException("All models should have the table name constant.");
        }
        return $const;
    }
}
