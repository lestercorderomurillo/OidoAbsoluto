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
        return preg_replace('[A-Za-z0-9@.\/\-#]', '', trim($string));
    }

    /**
     * Check if a string contains another string. Another way to say it's substring of...
     * 
     * @param string $text The input string.
     * @param string $check The string to search.
     * 
     * @return bool Return true if the string contains the another one, false otherwise.
     */
    public static function contains(string $text, string $check)
    {
        return (strpos($text, $check) !== false);
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
    public static function quotedExplode(string $text, array $delimiters = [" "], array $quotes = ["\"'"]): array
    {
        $delimiters = implode('', $delimiters);
        $clauses[] = '[^' . $delimiters . $quotes . ']';

        foreach ($quotes as $quote) {
            $quote = self::sanitizeString($quote);
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

    /**
     * Checks if the given string starts with another one.
     * The search will start at the offset position.
     * 
     * @param string $text The input string.
     * @param string $check The string to search.
     * @param int $offset The initial offset of the search.
     * 
     * @return bool True if it does, false otherwise.
     */
    public static function startsWith(string $text, string $check, int $offset = 0): bool
    {
        $length = strlen($check);
        return substr($text, $offset, $length) === $check;
    }

    /**
     * Checks if the given string ends with another one.
     * 
     * @param string $text The input string.
     * @param string $check The string to search.
     * 
     * @return bool True if it does, false otherwise.
     */
    public static function endsWith(string $text, string $check): bool
    {
        $length = strlen($check);
        if (!$length) {
            return true;
        }
        return substr($text, -$length) === $check;
    }
}
