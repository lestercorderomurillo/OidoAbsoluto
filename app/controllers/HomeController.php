<?php

namespace App\Controllers;

use App\Models\Answer;
use App\Models\User;
use App\Models\UserInfo;
use Cosmic\Binder\Authorization;
use Cosmic\FileSystem\Paths\File;
use Cosmic\Core\Bootstrap\Controller;
use Cosmic\ORM\Bootstrap\Database;
use Cosmic\ORM\Databases\SQL\SQLDatabase;
use Cosmic\Utilities\Collection;
use Cosmic\Utilities\Cryptography;
use PHPMailer\PHPMailer\PHPMailer;

class HomeController extends Controller
{
    private Database $db;

    function __construct(SQLDatabase $db)
    {
        $this->db = $db;
    }

    function login()
    {
        if (Authorization::isLogged()) {

            return $this->automaticSurveyProfileRedirect();
        }

        return $this->view();
    }

    function loginSubmit(string $email, string $password)
    {

        if (Authorization::isLogged()) {

            Authorization::logOut();
            $this->error("Se ha detectado actividad sospechosa, por lo que deberá logearse de nuevo.");
            return $this->redirect("login");
            
        }

        if (Authorization::tryLogIn($email, $password, User::class)) {
            return $this->redirect("profile");
        }

        $this->error("El usuario o contraseña ingresada no son correctos.");
        return $this->view("login");
    }

    function logout()
    {
        Authorization::logout();
        return $this->redirect();
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

                $this->error("No se puede validar los datos ingresados en el servidor remoto. ");
                return $this->redirect("signup");
            }
        } else {

            $this->error("No se puede registrar el usuario ingresado porque el correo utilizado se encuentra asociado a otra cuenta ya existente.");
            return $this->redirect("signup");
        }
    }

    function userExists(string $email)
    {
        $result = $this->db->find(User::class, ["email" => "$email"]);
        return ($result != null);
    }

    function automaticSurveyProfileRedirect()
    {
        if (!$this->db->exists(Answer::class, ["id" => Authorization::getCurrentId()])) {
            return $this->redirect("survey");
        }

        return $this->redirect("profile");
    }

    function resetRequest()
    {
        return $this->view();
    }

    function resetRequestSubmit(string $email)
    {
        $user = $this->db->find(User::class, ["email" => $email]);

        if($user != null){

            $user->token = password_hash($user->salt . $email, PASSWORD_BCRYPT);

            $this->db->save($user);
            $this->db->commit($user);

            $link = __HOST__ . "newpass?token=" . $user->token;

            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->Mailer = "smtp";
            $mail->SMTPDebug  = 1;  
            $mail->SMTPAuth   = TRUE;
            $mail->SMTPSecure = "tls";
            $mail->Port       = 587;
            $mail->Host       = "smtp.gmail.com";
            $mail->Username   = "oidoabsolutocr@gmail.com";
            $mail->Password   = "Hlj3sCJroVm";
            $mail->IsHTML(true);
            $mail->AddAddress($email, "recipient-name");
            $mail->SetFrom("oidoabsolutocr@gmail.com", "Servicio Oido Absoluto");
            $mail->Subject = "Solicitud de recuperación de contraseña";
            $content = <<<HTML
                <b>Si usted solicitó el cambio de su contraseña, presione el siguiente enlace: <br>$link</b>
                <br>
                <span>Si no solicitó esto, solamente ignore este mensaje</span>
            HTML;
            $mail->CharSet = 'UTF-8';
            $mail->MsgHTML($content); 
            $mail->Send();

        }

        $this->info("Si el correo electrónico que usted proporcionó existe en nuestro sistema, se le enviará un mensaje con instrucciones de recuperación.");
        return $this->redirect("index");
    }

    function resetPassword(string $token)
    {

        $user = $this->db->find(User::class, ["token" => $token]);

        if ($token == __EMPTY__ || $user == null) {
            return $this->error("El token proporcionado es invalido para este contexto.");
            return $this->redirect();
        }


        return $this->view(["token" => $token]);
    }

    function resetPasswordSubmit(string $token, string $password, string $confirmPassword)
    {
        Authorization::logOut();

        $user = $this->db->find(User::class, ["token" => $token]);

        if ($token == __EMPTY__ || $password == __EMPTY__ || $confirmPassword == __EMPTY__  || $user == null) {

            $this->error("No se puede cambiar la contraseña en este contexto.");
            return $this->redirect();

        }

        if ($password != $confirmPassword) {

            $this->error("Las contraseñas no coinciden.");
            return $this->redirect();

        }

        $user->salt = Cryptography::computeRandomKey(32);
        $user->password = password_hash($user->salt . $password, PASSWORD_BCRYPT);
        $user->token = password_hash($user->salt . $user->email, PASSWORD_BCRYPT);

        $this->db->save($user);
        $this->db->commit($user);
        
        $this->success("Se ha cambiado con éxito la contraseña. Intente acceder.");

        return $this->redirect();
    }
}
