<?php

namespace Pipeline\Security;

class Cryptography
{
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
