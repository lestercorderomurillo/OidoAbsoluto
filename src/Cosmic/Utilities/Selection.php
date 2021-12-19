<?php

namespace Cosmic\Utilities;

/**
 * This helper class is used to select substrings inside strings. 
 * Extremelly useful when doing string replacements and DOM manipulation on the server side.
 */
class Selection
{
    /**
     * @var string $value The original value without selection.
     */
    private string $value;

    /**
     * @var int $startPosition The start position of the selection.
     */
    private int $startPosition;

    /**
     * @var int $endPosition The end position of the selection.
     */
    private int $endPosition;

    /**
     * Selects the given string, starting at the given position and ending at the other position.
     * 
     * @param int $startPosition The initial position of the selection.
     * @param int $endPosition The final position of the selection.
     * @param string $value The string to select.
     * 
     * @return void
     */
    public function __construct(int $startPosition, int $endPosition, string $value)
    {
        $this->value = $value;
        $this->startPosition = max($startPosition, 0);
        $this->endPosition = min($endPosition, strlen($value));
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
    public function getString(): string
    {
        return trim(substr($this->value, $this->startPosition, $this->endPosition - $this->startPosition));
    }

    /**
     * Get the origin string used.
     * 
     * @return string The original string, without the selection.
     */
    public function getSourceString(): string
    {
        return $this->value;
    }

    /**
     * Get the current start position of the selection.
     * 
     * @return int The start position.
     */
    public function getStartPosition(): int
    {
        return $this->start;
    }

    /**
     * Get the current end position of the selection.
     * 
     * @return int The end position.
     */
    public function getEndPosition(): int
    {
        return $this->end;
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
        $this->start = max($position, 0);
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
        $this->end = $position;
        return $this;
    }

    /**
     * Move the start position for this selection. 
     * 
     * @param int $offset The relative position offset to apply.
     * 
     * @return Selection This instance with the changes.
     */
    public function moveStartPosition(int $offset = 1): Selection
    {
        $this->start += $offset;
        $this->start = max($this->start, 0);
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
        $this->end += $offset;
        return $this;
    }
}
