<?php

namespace VIP\Security;

class Cryptography
{
    public static function sanitizeString(string $string): string
    {
        return preg_replace('[A-Za-z0-9@.\/\-#]', '', trim($string));
    }

    public static function computeRandomKey(int $length = 16, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyz'): string
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}
