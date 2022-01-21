<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Security;

/**
 * Manages the state of the current user. Internally uses the session kernel function call.
 */
class Authorization
{
    const ADMIN = 0;
    const USER = 1;

    /**
     * Check if the user is logged in or not.
     * 
     * @return bool True if logged, false otherwise.
     */
    public static function isLogged(): bool
    {
        return session("isLogged");
    }

    /**
     * Return the current active user id.
     * 
     * @return int The user id.
     */
    public static function getCurrentId(): int
    {
        if (static::isLogged()) {
            return session("loggedId");
        }

        return -1;
    }

    /**
     * Return the current active role for this user.
     * 
     * Can be any of the following:
     * -1 = None
     * 0 = Admin
     * 1 = User
     * 
     * @return int The role number.
     */
    public static function getCurrentRole(): int
    {
        if (self::isLogged()) {
            return session("loggedRole");
        }

        return -1;
    }

    /**
     * Return the current secret key for this user.
     * 
     * @return string The secret key.
     */
    public static function getCurrentSecretKey(): string
    {
        if (self::isLogged()) {
            return session("secretKey");
        }

        return __EMPTY__;
    }

    /**
     * Check if the given username and password are valid for logging in.
     * The model schema MUST have salt and password fields.
     * 
     * @param string $token The token to validate.
     * @param string $password The password to validate.
     * @param string $className The model class to use.
     * @param string $dataSourceClassName The database className to retrieve from the IoC Container.
     * @param string $attribute The attribute to use for looking up the model in the database.
     * @return bool True if the given user is valid, false otherwise.
     */
    public static function tryLogIn(string $token, string $password, string $className, string $dataSourceClassName, string $attribute = "email"): bool
    {
        if (!self::isLogged()) {

            $db = app()->get($dataSourceClassName);

            $userModel = $db->find($className, ["$attribute" => $token]);

            if ($userModel != null) {

                if (Cryptography::verifyPassword($userModel->salt, $password, $userModel->password)) {

                    session("isLogged", true);
                    session("loggedId", $userModel->getId());
                    session("loggedRole", $userModel->role);
                    session("secretKey", Cryptography::computeRandomKey());

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Logout the current active session. Error messages will be kept.
     * 
     * @return void
     */
    public static function logOut(): void
    {
        $text = session("alertText");
        $type = session("alertType");
        
        session()->clear();

        if($text != __EMPTY__ && $type != __EMPTY__){

            session("alertText", $text);
            session("alertType", $type);

        }
    }
}
