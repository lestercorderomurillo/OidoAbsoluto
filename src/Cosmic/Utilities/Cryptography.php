<?php

namespace Cosmic\Utilities;

/**
 * This helper class is used to provide methods for cryptographic.
 */
class Cryptography
{
    /**
     * Compute a new random key using the provided length and character set.
     * 
     * @param int $length The length of the new random key.
     * @param string $keyspace Allowed characters to use in the generation.
     * 
     * @return string The generated pseudo-random key.
     */
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
