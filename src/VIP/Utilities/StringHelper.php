<?php

namespace VIP\Utilities;

class StringHelper
{
    public static function randomString(int $length = 16, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyz'): string
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    public static function sanitizeString(string $string): string
    {
        return preg_replace('[A-Za-z0-9@.\/\-#]', '', trim($string));
    }

    public static function quotedExplode(string $text, string $delimiters = " ", string $quotes = "\"'"): array
    {
        $clauses[] = '[^' . $delimiters . $quotes . ']';

        foreach (str_split($quotes) as $quote) {
            $quote = StringHelper::sanitizeString($quote);
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
