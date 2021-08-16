<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserInfo;

use VIP\Controller\BaseController;
use VIP\Factory\ResponseFactory;
use VIP\FileSystem\FilePath;
use VIP\FileSystem\FileSystem;
use VIP\HTTP\Server\Response\Response;
use VIP\HTTP\Server\Response\View;
use VIP\Security\Cryptography;

use function VIP\Core\Session;
use function VIP\Core\Services;

class HomeController extends BaseController
{
    function test()
    {
        $db = Services("SQLDatabase");

        $models = $db->findAll(UserInfo::class, ["gender" => "M"]);
        var_dump($models);
        $result = $db->find(UserInfo::class, ["id" => "1", "gender" => "M"]);
        var_dump($result->getInternalResult());

        return new Response();
    }

    function login()
    {
        return new View("login");
    }

    function signup()
    {
        $countries = FileSystem::requireFromFile(new FilePath(FilePath::DIR_COMMON, "countries", "php"));
        return new View("signup", ["countries" => $countries]);
    }

    function resetPassword(string $token)
    {
        if ($token == "") {
            return ResponseFactory::createError(500, "Invalid password reset token.");
        }
        return new View("reset-password");
    }

    function resetRequest()
    {
        return new View("reset-request");
    }

    function loginSubmit(string $email, string $password)
    {

        return new View("login");
    }

    function signupSubmit(
        string $first_name,
        string $last_name,
        string $email,
        string $password,
        string $confirm_password,
        string $country,
        string $birth_day,
        string $phone,
        string $gender
    ) {

        if (!$this->userExists($email) && $password == $confirm_password) {
            $db = services("SQLDatabase");

            $user = new User();
            $user->email = $email;
            $user->salt = Cryptography::computeRandomKey(32);
            $user->password = password_hash($user->salt . $password, PASSWORD_BCRYPT);
            $user->token = password_hash($user->salt . $email, PASSWORD_BCRYPT);
            $user->activated = 1;
            $db->save($user);

            $info = new UserInfo();
            $info->first_name = $first_name;
            $info->last_name = $last_name;
            $info->country = $country;
            $info->birth_day = $birth_day;
            $info->phone = $phone;
            $info->gender = $gender;
            $db->save($info);
            $db->commit();

            Session()->store("type", "success");
            Session()->store("message", "Su usuario se ha registrado correctamente. 
            Pruebe a iniciar sesión con sus nuevos credenciales.");

            return new View("login");
        } else {

            Session()->store("type", "danger");
            Session()->store("message", "No se puede registrar el usuario ingresado.");

            return new View("submit");
        }
    }

    function userExists(string $email)
    {
        $db = services("SQLDatabase");
        $result = $db->find(User::class, ["email" => "$email"]);
        $result->getInternalResult();
        return ($result != NULL);
    }

    function profile(){
        return new View("profile");
    }
}
