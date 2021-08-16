<?php

namespace VIP\Utilities;

use DOMDocument;
use VIP\Security\Cryptography;

class StringHelper
{
    public static function quotedExplode(string $text, string $delimiters = " ", string $quotes = "\"'"): array
    {
        $clauses[] = '[^' . $delimiters . $quotes . ']';

        foreach (str_split($quotes) as $quote) {
            $quote = Cryptography::sanitizeString($quote);
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
}
