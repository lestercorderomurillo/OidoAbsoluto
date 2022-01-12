<?php

namespace Cosmic\Utilities;

/**
 * This helper class is used to provide methods to search and manipulate string patterns.
 */
class Pattern
{
    /**
     * Select a string using the given delimiters. Will return on the first match.
     * 
     * @param string $input The input string to check for.
     * @param string $startDelimiter The opening delimiter.
     * @param string $endDelimiter The closure delimiter.
     * @param int $offset The starting position to start the searching.
     * 
     * @return Selection
     * 
     */
    public static function select(string $input, string $startDelimiter = "{", string $endDelimiter = "}", int $offset = 0): Selection
    {
        $offset = min($offset, strlen($input));

        $start = strpos($input, $startDelimiter, $offset);
        $end = false;

        if ($start !== false) {
            $end = strpos($input, $endDelimiter, $start) + 1;
        }

        return new Selection($input, $start, $end, $startDelimiter, $endDelimiter);
    }

    /**
     * Check if the given value is a valid number value.
     * This method returns -1 if the value is not present.
     * 
     * @param string $input The input string.
     * @param string $check The value to check inside the input string.
     * @param int $offset Initial offset for the input string.
     * 
     * @return int The position of the first character of the check string inside the input string.
     */
    public static function findByText(string $input, string $check, int $offset = 0): int
    {
        $position = min($offset, strlen($input));
        return strpos($input, $check, $position);
    }

    /**
     * Check if the given value is a valid number value.
     * 
     * @param mixed $value Any value that can be casted to a number.
     * 
     * @return bool True if the string contains a number.
     */
    public static function isNumber($value): bool
    {
        if (!isset($value)) return false;
        $int = (int)$value;

        if ((string)$int != (string)$value) {
            return false;
        }
        return true;
    }
}
