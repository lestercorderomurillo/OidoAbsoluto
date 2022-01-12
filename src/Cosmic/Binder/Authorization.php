<?php

namespace Cosmic\Binder;

use Cosmic\ORM\Databases\SQL\SQLDatabase;
use Cosmic\Utilities\Cryptography;

/**
 * Manages the state of the current user.
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
        return (session()->has("isLogged"));
    }

    /**
     * Return the current active user id.
     * 
     * @return int The user id.
     */
    public static function getCurrentId(): int
    {
        if (self::isLogged()) {
            return session()->get("loggedId");
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
            return session()->get("loggedRole");
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
            return session()->get("secretKey");
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
     * @param string $attribute The attribute to use for looking up the model in the database.
     * 
     * @return bool True if the given user is valid, false otherwise.
     */
    public static function tryLogIn(string $token, string $password, string $className, string $attribute = "email"): bool
    {
        if (!self::isLogged()) {

            $db = app()->get(SQLDatabase::class);

            $userModel = $db->find($className, ["$attribute" => $token]);

            if ($userModel != null) {

                if (password_verify($userModel->salt . $password, $userModel->password)) {

                    session()->add("isLogged", true);
                    session()->add("loggedId", $userModel->getId());
                    session()->add("loggedRole", $userModel->role);
                    session()->add("secretKey", Cryptography::computeRandomKey());

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Logout the current active session.
     * 
     * @return void
     */
    public static function logOut(): void
    {
        $text = session("alertText");
        $type = session("alertType");
        
        session()->clear();

        if($text != __EMPTY__ && $type != __EMPTY__){

            session()->add("alertText", $text);
            session()->add("alertType", $type);

        }
    }
}
