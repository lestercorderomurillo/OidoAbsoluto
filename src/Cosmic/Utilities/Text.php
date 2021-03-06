<?php

namespace Cosmic\Utilities;

/**
 * This helper class is used to provide methods for manipulating strings and text in general.
 */
class Text
{
    /**
     * Sanitanize the given string, allowing only A-z, 0-9 and some special characters.
     * 
     * @param string $string The string to sanitize.
     * 
     * @return string The sanitized string
     */
    public static function sanitizeString(string $string): string
    {
        //return $string;
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Returns the name of a resource given a namespace.
     * Ex: Collection/Lib/Object returns Object. Allows both kinds of slashes as delimiter.
     * 
     * @param string $resource The full name of a resource.
     * 
     * @return string The resource name.
     */
    public static function getNamespaceBaseName(string $resource): string
    {
        $resource = strtr($resource, ["\\" => "/"]);
        $lastIndex = strripos($resource, "/");

        if ($lastIndex == false) return $resource;

        return substr($resource, $lastIndex + 1);
    }

    /**
     * Check if a string contains another string. Another way to say it's substring of...
     * 
     * @param string $text The input string. 
     * @param string[]|string $check The string to search. Can be an array of strings.
     * 
     * @return bool Return true if the string contains the another one, false otherwise.
     */
    public static function contains(string $text, $check)
    {
        $check = Collection::normalize($check);

        foreach ($check as $singleCheck) {
            if (strpos($text, $singleCheck) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a string is equals to another string.
     * 
     * @param string $text The input string. 
     * @param string[]|string $check The string to search. Can be an array of strings.
     * 
     * @return bool Return true if the string contains the another one, false otherwise.
     */
    public static function equals(string $text, $check)
    {
        $check = Collection::normalize($check);

        foreach ($check as $singleCheck) {

            if ($text == $singleCheck) {
                return true;
            }
        }

        return false;
    }

    /**
     * Explode the given input string. This method will ignore all delimiters when inside a quote when transversing the string.
     * 
     * @param string $text The input string.
     * @param array $delimiters A collection of delimiters to use.
     * @param array $quotes A collection of quotes to use.
     * 
     * @return array Collection of values already splitted.
     */
    public static function quotedExplode(string $text, array $delimiters = [" "], array $quotes = ["\"", "'"]): array
    {
        $clauses[] = '[^' . implode("", $delimiters) . implode($quotes) . ']';

        foreach ($quotes as $quote) {
            $clauses[] = "[$quote][^$quote]*[$quote]";
        }

        $regex = '(?:' . implode('|', $clauses) . ')+';
        preg_match_all('/' . str_replace('/', '\\/', $regex) . '/', $text, $matches);

        return $matches[0];
    }

    /**
     * Explode a string using multiple delimiters. Return a collection with all of the splitted values.
     * 
     * @param array $delimiters A collection of delimiters.
     * @param string $text The input string to convert.
     * 
     * @return string The string converted to dashed case.
     */
    public static function multiExplode(array $delimiters, string $text): array
    {
        $ready = str_replace($delimiters, $delimiters[0], $text);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }

    /**
     * Convert the given input string from camelCase to dashed-case.
     * 
     * @param string $value The input string to convert.
     * 
     * @return string The string converted to dashed case.
     */
    public static function camelToDashed(string $value): string
    {
        preg_match_all('/[A-Z][a-z]+/', $value, $matches);
        return strtolower(implode('-', $matches[0]));
    }
}
