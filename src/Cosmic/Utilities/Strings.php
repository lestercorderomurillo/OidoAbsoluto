<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Utilities;

/**
 * This utility class provides methods for string manipulation.
 */
class Strings
{
    /**
     * Return the position of the first occurrence of given value in the input string.
     * 
     * @param string $input The input string.
     * @param string $check The value to find inside the input string.
     * @param int $offset Initial offset to be applied to the input string.
     * @return int The position of the first occurrence. Returns false if not found.
     */
    public static function firstOcurrence(string $input, string $check, int $offset = 0): int
    {
        return strpos($input, $check, min($offset, strlen($input)));
    }

    /**
     * Return the position of the last occurrence of given value in the input string.
     * 
     * @param string $input The input string.
     * @param string $check The value to find inside the input string.
     * @param int $offset Initial offset to be applied to the input string.
     * @return int|false The position of the last occurrence. Returns false if not found.
     */
    public static function lastOcurrence(string $input, string $check, int $offset = 0): int
    {
        return strrpos($input, $check, min($offset, strlen($input)));
    }

    /**
     * Sanitanize the given string, removing HTML characters.
     * 
     * @return string The sanitized string.
     */
    public static function sanitize(string $string): string
    {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Returns the name of a base class name from the given string.
     * Example: Collection/Lib/Object returns Object. 
     * Allows both kinds of slashes as delimiter.
     * 
     * @param string $className The full class name.
     * @return string The simplified name.
     */
    public static function getClassBaseName(string $className): string
    {
        $className = strtr($className, ["\\" => "/"]);
        $lastIndex = strripos($className, "/");

        if ($lastIndex == false) return $className;

        return substr($className, $lastIndex + 1);
    }

    /**
     * Check if the given string contains all of the given substrings.
     * 
     * @param string $text The input string. 
     * @param string[]|string $check The string to search. Can be an array of strings.
     * @return bool Return true if the string contains the another one, false otherwise.
     */
    public static function contains(string $text, $substrings)
    {
        $substrings = Collections::normalizeToList($substrings);

        foreach ($substrings as $substring) {
            if (strpos($text, $substring) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the given string contains at least one of the given substrings.
     * 
     * @param string $input The input string. 
     * @param string[]|string $substrings The input substrings. Will be automatically normalized if needed.
     * @return bool Return true if the string contains the another one, false otherwise.
     */
    public static function containsOne(string $input, $substrings)
    {
        $substrings = Collections::normalizeToList($substrings);

        foreach ($substrings as $substring) {
            if (strpos($input, $substring) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if given string is equal to at least one of the given substrings.
     * 
     * @param string $input The input string. 
     * @param string[]|string $substrings The input substrings. Will be automatically normalized if needed.
     * @return bool Return true if the string contains the another one, false otherwise.
     */
    public static function equals(string $input, $substrings)
    {
        $substrings = Collections::normalizeToList($substrings);

        foreach ($substrings as $substring) {
            if ($input === $substring) {
                return true;
            }
        }

        return false;
    }

    /**
     * Explode the given input string. This method will ignore all delimiters when inside a quote when transversing the string.
     * 
     * @param string $text The input string.
     * @param array $delimiters [Optional] A list of delimiters to use. Blank single space as default.
     * @param array $quotes [Optional] A list of quotes to use. Single and double quotes as default.
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
     * @return string The string converted to dashed case.
     */
    public static function camelToDashed(string $value): string
    {
        preg_match_all('/[A-Z][a-z]+/', $value, $matches);
        return strtolower(implode('-', $matches[0]));
    }
}
