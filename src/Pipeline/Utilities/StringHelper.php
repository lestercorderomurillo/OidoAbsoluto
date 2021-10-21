<?php

namespace Pipeline\Utilities;

class StringHelper
{
    public static function sanitizeString(string $string): string
    {
        return preg_replace('[A-Za-z0-9@.\/\-#]', '', trim($string));
    }

    public static function contains(string $text, string $check)
    {
        return (strpos($text, $check) !== false);
    }

    public static function quotedExplode(string $text, string $delimiters = " ", string $quotes = "\"'"): array
    {
        $clauses[] = '[^' . $delimiters . $quotes . ']';

        foreach (str_split($quotes) as $quote) {
            $quote = self::sanitizeString($quote);
            $clauses[] = "[$quote][^$quote]*[$quote]";
        }

        $regex = '(?:' . implode('|', $clauses) . ')+';
        preg_match_all('/' . str_replace('/', '\\/', $regex) . '/', $text, $matches);

        return $matches[0];
    }

    public static function camelToDashed(string $class_name): string
    {
        preg_match_all('/[A-Z][a-z]+/', $class_name, $matches);
        return strtolower(implode('-', $matches[0]));
    }

    public static function multiExplode(array $delimiters, string $text): array
    {
        $ready = str_replace($delimiters, $delimiters[0], $text);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }

    public static function parseMultivalueFields(array &$attributes): array
    {
        foreach ($attributes as $attribute => $value) {

            $multivalue = explode("&", $attribute);

            if (count($multivalue) > 1) {
                unset($attributes[$attribute]);
                foreach ($multivalue as $multi) {
                    $attributes[$multi] = $value;
                }
            }
        }

        ksort($attributes);
        return $attributes;
    }

    public static function startsWith(string $string, string $check, int $offset = 0): bool
    {
        $length = strlen($check);
        return substr($string, $offset, $length) === $check;
    }

    public static function endsWith(string $string, string $check): bool
    {
        $length = strlen($check);
        if (!$length) {
            return true;
        }
        return substr($string, -$length) === $check;
    }
}
