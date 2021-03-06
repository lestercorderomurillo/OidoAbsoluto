<?php

namespace Cosmic\ORM\Bootstrap;

use Cosmic\Utilities\Text;
use Cosmic\Traits\ClassAwareTrait;
use Cosmic\Traits\ValuesSetterTrait;

/**
 * This class represents a simple model. Developers should extend this class to make their own models.
 */
abstract class Model
{
    use ClassAwareTrait;
    use ValuesSetterTrait;

    /**
     * @var string $id The entity current Id key.
     */
    public string $id;

    /**
     * @var array $data The underlying stored data.
     */
    private array $data = [];

    /**
     * Constructor. By default, sets the Id to 0.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->setId(0);
    }

    /**
     * Sets the current entity to the given id.
     * 
     * @param string $id The entity id to use.
     * 
     * @return Model This instance.
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Return the entity current id stored in this model.
     * 
     * @return string The stored id for this entity.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Return the entity current stored values.
     * 
     * @return array The values of this model.
     */
    public function getValues(): array
    {
        return $this->getPublicProperties();
    }

    /**
     * Return the query replacement placeholder array for this model compiled.
     * Uses the class attributes to resolve the placeholder array.
     * 
     * @param bool $skipID If true, the placeholder will skip the id attribute.
     * 
     * @return string The placeholder array already compiled.
     */
    public function getAttributesPlaceholders(bool $skipID = false): string
    {
        $attributes = [];
        foreach ($this->getPublicProperties() as $property => $value) {
            if ($skipID) {
                if (strtolower($property) !== "id") {
                    $attributes[] = '`' . $property . '` = :' . $property;
                }
            } else {
                $attributes[] = '`' . $property . '` = :' . $property;
            }
        }

        return implode(", ", $attributes);
    }

    /**
     * Return the bindings with their respective values for this model.
     * 
     * @param bool $skipID If true, the placeholder will skip the id attribute.
     * 
     * @return array The attributes array.
     */
    public function getAttributesValues(bool $skipID = false): array
    {
        $attributes = [];
        foreach ($this->getPublicProperties() as $property => $value) {

            if ($skipID) {
                if (strtolower($property) !== "id") {
                    $attributes[":" . $property] = $value;
                }
            } else {
                $attributes[":" . $property] = $value;
            }
        }
        return $attributes;
    }

    /**
     * Gets the table name associated to this model, using the table constant.
     * If the constant is not present, returns the class name.
     * 
     * @return string The name of the table as in the database.
     */
    public function getTableName(): string
    {
        $const = $this->getConstant("table");

        if ($const === false) {
            return strtolower(Text::getNamespaceBaseName(static::class));
        }

        return $const;
    }
}
