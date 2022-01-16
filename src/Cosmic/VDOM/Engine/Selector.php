<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\VDOM\Engine;

use Cosmic\Traits\StringableTrait;
 
/**
 * This helper class is used to select substrings inside strings. Extremelly useful when doing string replacements.
 */
abstract class Selector
{
    use StringableTrait;

    /**
     * @var string $input The original input without selection.
     */
    protected string $input;

    /**
     * @var int $startPosition The start position of the selection.
     */
    protected int $startPosition;

    /**
     * @var int|null $endPosition The end position of the selection.
     */
    protected $endPosition;

     /**
     * Selects the given string, starting at the given position and ending at the other position.
     * 
     * @param string $input The string to select.
     * @param int|null $startPosition The initial position of the selection.
     * @param int|null $endPosition The final position of the selection.
     */
    public function __construct(string $input, $startPosition, $endPosition)
    {
        $this->input = $input;
        $this->startPosition = max($startPosition, 0);
        $this->endPosition = min($endPosition, strlen($input));
    }

    /**
     * Check if the current selection is still valid.
     * @return bool True if the selection is valid, false otherwise.
     */
    public function isValid(): bool
    {
        return ($this->endPosition != false);
    }

    /**
     * Get the origin string used.
     * @return string The original string, without the selection.
     */
    public abstract function getSourceString(): string;

    /**
     * @inheritdoc
     */
    public abstract function toString(): string;

    /**
     * Get the current start position of the selection.
     * @return int The start position.
     */
    public function getStartPosition(): int
    {
        return $this->startPosition;
    }

    /**
     * Get the current end position of the selection.
     * @return int|null The end position.
     */
    public function getEndPosition()
    {
        return $this->endPosition;
    }

    /**
     * Set the start position for this selection. 
     * 
     * @param int $position The new position to set.
     * @return Selector This instance with the changes.
     */
    public function setStartPosition(int $position): Selector
    {
        $this->startPosition = max($position, 0);
        return $this;
    }

    /**
     * Set the end position for this selection. 
     * 
     * @param int $position The new end position to set.
     * @return Selector This instance with the changes.
     */
    public function setEndPosition(int $position): Selector
    {
        $this->endPosition = $position;
        return $this;
    }

    /**
     * Move the start position for this selection. Moves +1 by default.
     * 
     * @param int $offset The relative position offset to apply.
     * @return Selector This instance with the changes.
     */
    public function moveStartPosition(int $offset = 1): Selector
    {
        $this->startPosition += $offset;
        $this->startPosition = max($this->startPosition, 0);
        return $this;
    }

    /**
     * Move the end position for this selection. 
     * 
     * @param int $offset The relative position offset to apply.
     * @return Selector This instance with the changes.
     */
    public function moveEndPosition(int $offset = 1): Selector
    {
        $this->endPosition += $offset;
        return $this;
    }
}
