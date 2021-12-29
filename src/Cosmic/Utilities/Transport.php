<?php

namespace Cosmic\Utilities;

/**
 * This helper class is used to provide methods to move objects and arrays as strings.
 */
class Transport
{
    /**
     * @var mixed $__temp Used when doing transcriptions; 
     */
    private static $__temp;

    /**
     * Convert the string to an array.
     * 
     * @param array $array The array to convert.
     *
     * @return string The encoded string.
     */
    public static function arrayToString(array $array): string
    {
        return "@_ARRAY_" . str_replace("=", "_", base64_encode(var_export($array, true)));
    }

    /**
     * Convert the array to a string.
     * 
     * @param string $string The string to convert.
     *
     * @return array The decoded array.
     */
    public static function stringToArray(string $string): array
    {
        $string = str_replace("@_ARRAY_", "", $string);
        $string = str_replace("_", "=", $string);
        $code = 'self::$__temp = ' . base64_decode($string);
        if (!Text::endsWith($code, ")")) {
            $code .= ")";
        }
        eval($code . ";");
        return self::$__temp;
    }
}
