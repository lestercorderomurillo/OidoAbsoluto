<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Types;

use Cosmic\Core\Interfaces\FactoryInterface;
use Cosmic\FileSystem\FS;
use Cosmic\Traits\StringableTrait;

/**
 * This class represents a json type. This class encapsulates useful methods for manipulating JSON objects with files.
 */
class JSON implements FactoryInterface
{
    use StringableTrait;

    /**
     * @var int $hints JSON hints.
     */
    private int $hints;

    /**
     * @var mixed $value The stored PHP native value.
     */
    private $value;

    /**
     * Constructor. Creates a new JSON object from anything.
     * 
     * @param mixed[] $value The data to be stored.
     * @param int $hints JSON Hints. [optional]
     * 
     * Bitmask consisting of:
     * JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS, 
     * JSON_NUMERIC_CHECK, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES, JSON_FORCE_OBJECT, 
     * JSON_UNESCAPED_UNICODE. JSON_THROW_ON_ERROR 
     * 
     * The behaviour of these constants is described on the JSON constants page of php documentation.
     */
    public function __construct($value, int $hints = 0)
    {
        $this->value = $value;
        $this->hints = $hints;
    }

    /**
     * Creates a new JSON object from a file path.
     * 
     * @param File|string $dataSource The file to parse from.
     * 
     * The behaviour of these constants is described on the JSON constants page of php documentation.
     * @return JSON An instance of JSON.
     * @inheritdoc
     */
    public static function from($dataSource): JSON
    {
        return new JSON(json_decode(FS::read($dataSource), true));
    }

    /**
     * Adds a new hint to this JSON object.
     * Returns a copy of this object.
     * 
     * Bitmask consisting of:
     * JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS, 
     * JSON_NUMERIC_CHECK, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES, JSON_FORCE_OBJECT, 
     * JSON_UNESCAPED_UNICODE. JSON_THROW_ON_ERROR 
     * 
     * @param int $hint The hint bitmask to add.
     * @return JSON The json instance.
     */
    public function addHint(int $hint): JSON
    {
        $this->hints += $hint;
        return $this;
    }

    /**
     * Returns this JSON object as a valid JSON string.
     * 
     * @return string The json string representation.
     */
    public function toString(): string
    {
        return json_encode($this->value, $this->hints);
    }

    /**
     * Returns this JSON object as a valid array.
     * 
     * @return array The internal array.
     */
    public function toArray(): array
    {
        return $this->value;
    }
}
