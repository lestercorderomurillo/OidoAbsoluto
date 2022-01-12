<?php

namespace Cosmic\Utilities;

/**
 * This helper class is used to provide methods to move objects and arrays as strings.
 */
class Transport
{
    /**
     * Convert the string to an array.
     * 
     * @param array $array The array to convert.
     *
     * @return string The encoded string.
     */
    public static function arrayToString(array $array): string
    {
        $json = json_encode($array);
        return "@ARR" . self::encodeBase64SafeURL($json);
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
        if(Text::startsWith($string, "@ARR")){
            $string = substr($string, strlen("@ARR"));
        }
        $decoded = json_decode(self::decodeBase64SafeURL($string), true);
        return $decoded;
    }

    /**
     * Convert the given string into a valid base64 encoded string.
     * 
     * @param string $input The string to convert.
     *
     * @return string The encoded string.
     */
    public static function encodeBase64SafeURL(string $input): string
    {
        return strtr(base64_encode($input), '+/=', '._-');
    }

    /**
     * Convert the given string into a valid base64 encoded string.
     * 
     * @param string $input The string to convert.
     *
     * @return string The encoded string.
     */
    public static function decodeBase64SafeURL(string $input): string
    {
        return base64_decode(strtr($input, '._-', '+/='));
    }
}
