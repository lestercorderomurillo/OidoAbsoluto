<?php

namespace Cosmic\Utilities;

use Cosmic\DOM\HTML\Selection;

/**
 * This helper class is used to provide methods to search and manipulate string patterns.
 */
class Pattern
{
    /**
     * Select a string using the given delimiters. Will return on the first match.
     * 
     * @param string $input The input string to check for.
     * @param string $openingDelimiter The opening delimiter.
     * @param string $closureDelimiter The closure delimiter.
     * @param int $offset The starting position to start the searching.
     * @param bool $selectDelimiters If true, selection will not select the delimiters.
     * 
     * @return Selection The selection.
     * 
     */
    public static function select(string $input, string $openingDelimiter = "{", string $closureDelimiter = "}", int $offset = 0, bool $selectDelimiters = false): Selection
    {
        $offset = min($offset, strlen($input));

        $start = strpos($input, $openingDelimiter, $offset);
        $end = false;

        if ($start !== false) {
            $end = strpos($input, $closureDelimiter, $start);
        }
        
        if (!$selectDelimiters) {
            $start += strlen($openingDelimiter);
            $end -= strlen($closureDelimiter);
        }

        return new Selection($start, $end, $input);
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

    /**
     * @deprecated
     */
    public static function substituteTokens($array_or_string, array $replacers, string $match_start = "{", string $match_end = "}", bool $ignore_arrays = false)
    {
        if (is_array($array_or_string)) {
            $replaced_array = $array_or_string;
        } else if (is_string($array_or_string)) {
            $replaced_array = [$array_or_string];
        }
        if (isset($replacers)) {
            foreach ($replaced_array as $key => $value) {
                $acumulative_replaced_value = $value;

                if (Collection::is2Dimensional($replacers)) {
                    foreach ($replacers as $_key => $_value) {
                        foreach ($_value as $__key => $__value) {
                            if ($ignore_arrays) {
                                if (!is_array($__value)) {
                                    $acumulative_replaced_value = str_replace($match_start . $__key . $match_end, $__value, "$acumulative_replaced_value");
                                }
                            } else {
                                $acumulative_replaced_value = str_replace($match_start . $__key . $match_end, $__value, "$acumulative_replaced_value");
                            }
                        }
                    }
                } else {
                    foreach ($replacers as $_key => $_value) {
                        if ($ignore_arrays) {
                            if (!is_array($_value)) {
                                $acumulative_replaced_value = str_replace($match_start . $_key . $match_end, $_value, "$acumulative_replaced_value");
                            }
                        } else {
                            $acumulative_replaced_value = str_replace($match_start . $_key . $match_end, $_value, "$acumulative_replaced_value");
                        }
                    }
                }


                $replaced_array["$key"] = $acumulative_replaced_value;
            }
        }

        if (is_array($array_or_string)) {
            return $replaced_array;
        } else if (is_string($array_or_string)) {
            return $replaced_array[0];
        }
    }

    /**
     * @deprecated
     */
    public static function selectStringByQuotes(string $source, string $opening = "{", string $closure = "}", int $search_offset = 0, int $after_select_offset = -1): Selection
    {
        $search_offset = min($search_offset, strlen($source));

        $start = strpos($source, $opening, $search_offset);
        $end = false;

        if ($start !== false) {
            $cursor = $start + strlen($opening);
            $end = strpos($source, $closure, $cursor);
        }

        if ($after_select_offset == -1) {
            $start += strlen($opening);
        } else {
            $start += $after_select_offset;
        }

        return new Selection($start, $end, $source);
    }
}
