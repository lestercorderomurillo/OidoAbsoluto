<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserInfo;
use Cosmic\FileSystem\Paths\File;
use Cosmic\Core\Controllers\Controller;
use Cosmic\Database\Bootstrap\Database;
use Cosmic\Database\SQLDatabase;
use Cosmic\Utilities\Collection;
use Cosmic\Utilities\Cryptography;

class HomeController extends Controller
{
    private Database $db;

    function __construct(SQLDatabase $db)
    {
        $this->db = $db;
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
            return $this->response(500, "Invalid password reset token.");
        }

        return $this->view("reset-password");
    }

    function resetPasswordSubmit(string $token)
    {
        if ($token == "") {
            return $this->response(500, "Invalid password reset token.");
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
        $countries = Collection::from(new File("src/Cosmic/Bundle/Common/countries.json"));
        return $this->view("signup", ["countries" => $countries]);
    }

    function signupSubmit(
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        string $confirmPassword,
        string $country,
        string $birthDay,
        string $phone,
        string $gender
    ) {

        if (!$this->userExists($email) && $password == $confirmPassword) {

            $user = new User();

            $user->email = $email;
            $user->salt = Cryptography::computeRandomKey(32);
            $user->password = password_hash($user->salt . $password, PASSWORD_BCRYPT);
            $user->token = password_hash($user->salt . $email, PASSWORD_BCRYPT);
            $user->activated = 1;

            $this->db->save($user);

            $info = new UserInfo();

            $info->firstName = $firstName;
            $info->lastName = $lastName;
            $info->country = $country;
            $info->birthDay = $birthDay;
            $info->phone = $phone;
            $info->gender = $gender;

            $this->db->save($info);
            $this->db->commit();

            $this->success("Su usuario se ha registrado correctamente. Pruebe a iniciar sesión con sus nuevos credenciales.");
            return $this->view("login");
            
        } else {

            $this->danger("No se puede registrar el usuario ingresado.", "danger");
            return $this->view("submit");
        }
    }

    function userExists(string $email)
    {
        $result = $this->db->find(User::class, ["email" => "$email"]);

        return ($result != null);
    }
}
