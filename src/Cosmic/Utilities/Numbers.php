<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Utilities;

/**
 * This utility class provides methods for number manipulation.
 */
class Numbers
{
    /**
     * Check if the given value is a valid number value.
     * 
     * @param mixed $value Any value that can be casted to a number.
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
     * Change the base of the given scalar number.
     * 
     * @param mixed $value Any value that can be casted to a number.
     * @param int $base [Optional] The number base. Default is 10.
     * @return int The int with a new number base.
     */
    public static function changeBase(float $value, int $base = 10): int
    {
        return intval($value, $base);
    }
}
