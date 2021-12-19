<?php

namespace Cosmic\Core\Types;

use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\File;
use Cosmic\Traits\StringableTrait;

/**
 * This class encapsulates methods useful for manipulating JSON objects and files.
 */
class JSON
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
     * Creates a new JSON object from the given value.
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
     * 
     * @return JSON A instance of JSON.
     */
    public static function create($value, int $hints = 0): JSON
    {
        $instance = new JSON($value, $hints);
        return $instance;
    }

    /**
     * Creates a new JSON object from a file path.
     * 
     * @param File $file The file to parse from.
     * @param int $hints JSON Hints. [optional]
     * 
     * Bitmask consisting of:
     * JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS, 
     * JSON_NUMERIC_CHECK, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES, JSON_FORCE_OBJECT, 
     * JSON_UNESCAPED_UNICODE. JSON_THROW_ON_ERROR 
     * 
     * The behaviour of these constants is described on the JSON constants page of php documentation.
     * 
     * @return JSON An instance of JSON.
     */
    public static function from(File $file, int $hints = 0): JSON
    {
        $instance = new JSON(json_decode(FileSystem::read($file), true), $hints);
        return $instance;
    }

    /**
     * Adds a new hint to this JSON object.
     * Returns a copy of this object.
     * 
     * @param int $hint The hint to add.
     * 
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
