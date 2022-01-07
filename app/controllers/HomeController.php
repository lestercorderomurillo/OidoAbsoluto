<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UserInfo;
use Cosmic\Bundle\Middlewares\Authentication;
use Cosmic\FileSystem\Paths\File;
use Cosmic\Core\Bootstrap\Controller;
use Cosmic\ORM\Bootstrap\Database;
use Cosmic\ORM\Databases\SQL\SQLDatabase;
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
        if (Authentication::isLogged()){
            return $this->redirect("/profile");
        }

        return $this->view();
    }

    function loginSubmit(string $email, string $password)
    {
        if (Authentication::tryLogIn($email, $password)){
            return $this->redirect("/profile");
        }

        $this->danger("El usuario o contraseña ingresada no son correctos.");
        return $this->view("login");
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

        if (!$this->userExists($email)) {

            if ($password == $confirmPassword) {

                // Create the models
                $user = new User();
                $info = new UserInfo();

                // Fill the user model
                $user->email = $email;
                $user->salt = Cryptography::computeRandomKey(32);
                $user->password = password_hash($user->salt . $password, PASSWORD_BCRYPT);
                $user->token = password_hash($user->salt . $email, PASSWORD_BCRYPT);
                $user->activated = 1;
                $user->role = 1;

                // Save the user model
                $this->db->save($user);

                $result = $this->db->commit();

                // Fill the info model
                $info->id = $result[0]->getInsertedId();
                $info->firstName = $firstName;
                $info->lastName = $lastName;
                $info->country = $country;
                $info->birthDay = $birthDay;
                $info->phone = $phone;
                $info->gender = $gender;

                // Save the user info model
                $this->db->save($info);
                $this->db->commit();

                $this->success("Su usuario se ha registrado correctamente. Pruebe a iniciar sesión con sus nuevos credenciales a continuación.");
                return $this->redirect("login");

            } else {

                $this->danger("No se puede validar los datos ingresados en el servidor remoto. ");
                return $this->redirect("signup");
            }

        } else {

            $this->danger("No se puede registrar el usuario ingresado porque el correo utilizado se encuentra asociado a otra cuenta ya existente.");
            return $this->redirect("signup");
        }
    }

    function userExists(string $email)
    {
        $result = $this->db->find(User::class, ["email" => "$email"]);
        return ($result != null);
    }










    // WIP

    function resetPassword(string $token)
    {
        if ($token == __EMPTY__) {
            return $this->response(500, "Invalid password reset token.");
        }

        return $this->view();
    }

    function resetRequest()
    {
        return $this->view();
    }

    function resetPasswordSubmit(string $token)
    {
        if ($token == "") {
            return $this->response(500, "Invalid password reset token.");
        }

        return $this->view("reset-password");
    }

    function resetRequestSubmit()
    {
        return $this->view("reset-request");
    }
}
