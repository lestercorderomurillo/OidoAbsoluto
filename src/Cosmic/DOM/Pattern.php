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

}
