<?php

namespace Cosmic\Binder;

use Cosmic\ORM\Databases\SQL\SQLDatabase;

/**
 * Manages the state of the current user.
 */
class Authorization
{
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

        return "NoUser";
    }

    /**
     * Return the current active role for this user.
     * 
     * Can be any of the following:
     * 0 = None
     * 1 = User
     * 2 = Admin
     * 
     * @return int The role number.
     */
    public static function getCurrentRole(): int
    {
        if (!session()->has("isLogged")) {
            return session()->get("loggedRole");
        }

        return 0;
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
        if (!session()->has("isLogged")) {

            $db = app()->get(SQLDatabase::class);

            $userModel = $db->find($className, ["$attribute" => $token]);

            if ($userModel != null) {

                if (password_verify($userModel->salt . $password, $userModel->password)) {

                    session()->add("isLogged", true);
                    session()->add("loggedId", $userModel->getId());
                    session()->add("loggedRole", $userModel->role);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * LogIn the current active session using a role and an username.
     * 
     * @param int $username The user ID to log in.
     * @param int $role The role to set the current active session.
     * 
     * @return bool True if successfully logged out, false otherwise.
     */
    public static function logIn(int $id, int $role): bool
    {
        if (!session()->has("isLogged")) {

            session()->add("isLogged", true);
            session()->add("loggedId", $id);
            session()->add("loggedRole", $role);

            return true;
        }

        return false;
    }

    /**
     * Logout the current active session.
     * 
     * @return bool True if successfully logged out, false otherwise.
     */
    public static function logOut(): bool
    {
        if (session()->has("isLogged")) {

            session()->delete("isLogged");
            session()->delete("loggedId");
            session()->delete("loggedRole");

            return true;
        }

        return false;
    }
}
