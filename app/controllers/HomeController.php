<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserInfo;
use Pipeline\Controller\Controller;
use Pipeline\Database\AbstractDatabase;
use Pipeline\FileSystem\FileSystem;
use Pipeline\Security\Cryptography;
use Pipeline\Factory\ResponseFactory;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\FileSystem\Path\Local\FilePath;
use Pipeline\HTTP\Server\ServerResponse;

use function Pipeline\Accessors\Dependency;
use function Pipeline\Accessors\Session;

class HomeController extends Controller
{
    private AbstractDatabase $db;

    function __construct(){
        $this->db = Dependency("Db");
    }
    
    function login()
    {
        return $this->view("login");
    }

    function loginSubmit(string $email, string $password)
    {
        return $this->view("login");
    }

    function resetPassword(string $token)
    {
        if ($token == "") {
            return ServerResponse::create(500, "Invalid password reset token.");
        }
        return $this->view("reset-password");
    }

    function resetPasswordSubmit(string $token)
    {
        if ($token == "") {
            return ServerResponse::create(500, "Invalid password reset token.");
        }
        return $this->view("reset-password");
    }

    function resetRequest()
    {
        return $this->view("reset-request");
    }

    function resetRequestSubmit()
    {
        return $this->view("reset-request");
    }

    function signup()
    {
        $countries = FileSystem::requireFromFile(new FilePath(SystemPath::COMMON, "countries", "php"));
        return $this->view("signup", ["countries" => $countries]);
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

            $user = new User();

            $user->email = $email;
            $user->salt = Cryptography::computeRandomKey(32);
            $user->password = password_hash($user->salt . $password, PASSWORD_BCRYPT);
            $user->token = password_hash($user->salt . $email, PASSWORD_BCRYPT);
            $user->activated = 1;

            $this->db->save($user);

            $info = new UserInfo();

            $info->first_name = $first_name;
            $info->last_name = $last_name;
            $info->country = $country;
            $info->birth_day = $birth_day;
            $info->phone = $phone;
            $info->gender = $gender;

            $this->db->save($info);
            $this->db->commit();

            Session("type", "success");
            Session("message", "Su usuario se ha registrado correctamente. Pruebe a iniciar sesión con sus nuevos credenciales.");

            return $this->view("login");

        } else {

            Session("type", "danger");
            Session("message", "No se puede registrar el usuario ingresado.");

            return $this->view("submit");
        }
    }

    function userExists(string $email)
    {
        $result = $this->db->find(User::class, ["email" => "$email"]);
        $result->expose();
        return ($result != []);
    }
}
