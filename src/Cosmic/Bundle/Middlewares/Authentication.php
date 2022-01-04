<?php

namespace Cosmic\Bundle\Middlewares;

use App\Models\User;
use App\Models\UserInfo;
use Cosmic\HTTP\Request;
use Cosmic\Core\Bootstrap\Middleware;
use Cosmic\ORM\Databases\SQL\SQLDatabase;

/**
 * When used, this middleware will ensure that if the user is not logged, put a message and redirect to another page.
 */
class Authentication extends Middleware
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
     * Return the current active username.
     * 
     * @return string The username.
     */
    public static function getCurrentUsername(): string
    {
        if (self::isLogged()) {
            return session()->get("loggedUsername");
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
     * 
     * @param string $email The email to validate.
     * @param string $password The password to validate.
     * 
     * @return bool True if the given user is valid, false otherwise.
     */
    public static function tryLogIn(string $email, string $password): bool
    {
        if (!session()->has("isLogged")) {

            $db = app()->get(SQLDatabase::class);

            /** @var User $model */
            $model = $db->find(User::class, ["email" => $email]);

            if ($model != null) {

                /** @var UserInfo $infoModel */
                $infoModel = $db->find(UserInfo::class, ["id" => $model->getId()]);

                if (password_verify($model->salt . $password, $model->password)) {

                    session()->add("isLogged", true);
                    session()->add("loggedUsername", $infoModel->firstName . " " . $infoModel->lastName);
                    session()->add("loggedRole", $model->role);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * LogIn the current active session using a role and an username.
     * 
     * @param string $username The username to log in.
     * @param int $role The role to set the current active session.
     * 
     * @return bool True if successfully logged out, false otherwise.
     */
    public static function logIn(string $username, int $role): bool
    {
        if (!session()->has("isLogged")) {

            session()->add("isLogged", true);
            session()->add("loggedUsername", $username);
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
            session()->delete("loggedUsername");
            session()->delete("loggedRole");

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function handle(Request $request): Request
    {
        if (!self::isLogged()) {
            $this->danger("Acceso denegado (sin autorización).");
            return $this->redirect("index");
        }

        return $request;
    }
}
