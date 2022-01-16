<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Utilities;

/**
 * This utility class provides methods for transfering and encoding.
 */
class Transport
{
    /**
     * Convert the given string into a valid base64 encoded string.
     * 
     * @param string $input The string to convert.
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
     * @return string The encoded string.
     */
    public static function decodeBase64SafeURL(string $input): string
    {
        return base64_decode(strtr($input, '._-', '+/='));
    }
}
