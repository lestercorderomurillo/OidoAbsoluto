<?php

namespace Cosmic\Utilities;

use Cosmic\Traits\StringableTrait;

/**
 * This helper class is used to select substrings inside strings. 
 * Extremelly useful when doing string replacements and Binder manipulation on the server side.
 */
class Selection
{
    use StringableTrait;

    /**
     * @var string $input The original input without selection.
     */
    private string $input;

    /**
     * @var int $startPosition The start position of the selection.
     */
    private int $startPosition;

    /**
     * @var int $endPosition The end position of the selection.
     */
    private int $endPosition;

    /**
     * @var string $startDelimiter The delimiter used to start the selection.
     */
    private string $startDelimiter;

    /**
     * @var string $endDelimiter The delimiter used to end the selection.
     */
    private string $endDelimiter;

    /**
     * Selects the given string, starting at the given position and ending at the other position.
     * 
     * @param string $input The string to select.
     * @param int|null $startPosition The initial position of the selection.
     * @param int|null $endPosition The final position of the selection.
     * @param string $startDelimiter The initial delimiter used to select this string. Can be left blank
     * @param string $endDelimiter The final delimiter used to select this string. Can be left blank
     * 
     * @return void
     */
    public function __construct(string $input, $startPosition, $endPosition, string $startDelimiter = "{", string $endDelimiter = "}")
    {
        $this->input = $input;
        $this->startPosition = max($startPosition, 0);
        $this->endPosition = min($endPosition, strlen($input));
        $this->startDelimiter = $startDelimiter;
        $this->endDelimiter = $endDelimiter;
    }

    /**
     * Check if the current selection is still valid.
     * 
     * @return bool True if the selection is valid, false otherwise.
     */
    public function isValid(): bool
    {
        return ($this->endPosition != false);
    }

    /**
     * Get the resulting string after performing the selection.
     * 
     * @return string The "cutted" string. 
     */
    public function getString(bool $excludeDelimiters = true): string
    {
        if($excludeDelimiters){
            $startPosition = $this->startPosition + strlen($this->startDelimiter);
            $endPosition = $this->endPosition - strlen($this->endDelimiter);

            return trim(substr($this->input, $startPosition, $endPosition - $startPosition));
        }
        return trim(substr($this->input, $this->startPosition, $this->endPosition - $this->startPosition));
    }

    /**
     * Get the origin string used.
     * 
     * @return string The original string, without the selection.
     */
    public function getSourceString(): string
    {
        return $this->input;
    }

    /**
     * @inheritdoc
     */
    public function toString(): string
    {
        return $this->getString();
    }

    /**
     * Get the current start position of the selection.
     * 
     * @return int The start position.
     */
    public function getStartPosition(): int
    {
        return $this->startPosition;
    }

    /**
     * Get the current end position of the selection.
     * 
     * @return int The end position.
     */
    public function getEndPosition(): int
    {
        return $this->endPosition;
    }

    /**
     * Set the start position for this selection. 
     * 
     * @param int $position The new position to set.
     * 
     * @return Selection This instance with the changes.
     */
    public function setStartPosition(int $position): Selection
    {
        $this->startPosition = max($position, 0);
        return $this;
    }

    /**
     * Set the end position for this selection. 
     * 
     * @param int $position The new end position to set.
     * 
     * @return Selection This instance with the changes.
     */
    public function setEndPosition(int $position): Selection
    {
        $this->endPosition = $position;
        return $this;
    }

    /**
     * Move the start position for this selection. Moves +1 by default.
     * 
     * @param int $offset The relative position offset to apply.
     * 
     * @return Selection This instance with the changes.
     */
    public function moveStartPosition(int $offset = 1): Selection
    {
        $this->startPosition += $offset;
        $this->startPosition = max($this->startPosition, 0);
        return $this;
    }

    /**
     * Move the end position for this selection. 
     * 
     * @param int $offset The relative position offset to apply.
     * 
     * @return Selection This instance with the changes.
     */
    public function moveEndPosition(int $offset = 1): Selection
    {
        $this->endPosition += $offset;
        return $this;
    }
}
