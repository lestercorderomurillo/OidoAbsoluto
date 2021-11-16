<?php

namespace Pipeline\Core\Boot;

use Pipeline\Core\Exceptions\InvalidModelException;
use Pipeline\Traits\ClassAwareTrait;
use Pipeline\Traits\ValuesSetterTrait;

abstract class Model
{
    use ClassAwareTrait;
    use ValuesSetterTrait;

    private string $id;
    private array $data = [];

    public function __construct()
    {
        $this->setId(0);
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
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
