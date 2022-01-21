<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Security;

/**
 * This class provides cryptography methods.
 */
class Cryptography
{
    /**
     * Compute a new random key using the provided length and character set.
     * 
     * @param int $length The length of the new random key.
     * @param string $keyspace Allowed characters to use in the generation.
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

    /**
     * Hashes the given password using the given salt.
     * Uses the PASSWORD_BCRYPT algorithm by default.
     * 
     * @param string $salt The salt to use.
     * @param string $password The password to hash.
     * @param string $algorithm [optional] The algorithm to use. Default is BCrypt.
     * @return string The hashed password.
     */
    public static function hashPassword(string $salt, string $password, string $algorithm = PASSWORD_BCRYPT): string
    {
        return password_hash($salt . $password, $algorithm);
    }

    /**
     * Verify the given password matches with the given salt and hash.
     * Uses the PASSWORD_BCRYPT algorithm by default.
     * 
     * @param string $salt The salt to use.
     * @param string $password The password to analyze.
     * @param string $hashedPassword The hashed password to check.
     * @return bool If it matches, false otherwise.
     */
    public static function verifyPassword(string $salt, string $password, $hashedPassword): bool
    {
        return password_verify($salt . $password, $hashedPassword);
    }

    
}
