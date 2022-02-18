<?php

namespace App\Controllers;

use App\Models\Answer;
use App\Models\User;
use App\Models\UserInfo;
use Cosmic\Binder\Authorization;
use Cosmic\Bundle\Common\Language;
use Cosmic\FileSystem\Paths\File;
use Cosmic\Core\Bootstrap\Controller;
use Cosmic\ORM\Bootstrap\Database;
use Cosmic\ORM\Databases\SQL\SQLDatabase;
use Cosmic\Utilities\Collection;
use Cosmic\Utilities\Cryptography;
use Cosmic\Utilities\Text;
use PHPMailer\PHPMailer\PHPMailer;

class HomeController extends Controller
{
    private Database $db;

    function __construct(SQLDatabase $db)
    {
        $this->db = $db;
    }

    function lang(string $set)
    {
        if (Text::contains($set, ['es', 'en'])) {
            session('lang', $set);
        }

        return $this->redirect("index");
    }

    function index()
    {
        if (Authorization::isLogged()) {

            if (Authorization::getCurrentRole() == Authorization::USER) {
                if (!$this->db->exists(Answer::class, ["id" => Authorization::getCurrentId()])) {
                    return $this->redirect("survey");
                }
            }

            return $this->redirect("profile");
        }

        return $this->redirect("login");
    }

    function login()
    {
        return $this->view();
    }

    function loginSubmit(string $email, string $password)
    {
        if (!Authorization::tryLogIn($email, $password, User::class)) {
            $this->error(Language::getString("misc00"));
            return $this->view("login");
        }

        return $this->redirect("profile");
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
                $user->role = Authorization::USER;

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

                $this->success(Language::getString("misc01"));
                return $this->redirect("login");
            } else {

                $this->error(Language::getString("misc02"));
                return $this->redirect("signup");
            }
        } else {

            $this->error(Language::getString("misc03"));
            return $this->redirect("signup");
        }
    }

    function userExists(string $email)
    {
        $result = $this->db->find(User::class, ["email" => "$email"]);
        return ($result != null);
    }

    function resetRequest()
    {
        return $this->view();
    }

    function resetRequestSubmit(string $email)
    {
        $user = $this->db->find(User::class, ["email" => $email]);

        if ($user != null) {

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
            $mail->Subject = Language::getString("misc04");

            $misc05 = Language::getString("misc05");
            $misc06 = Language::getString("misc06");

            $content = <<<HTML
                <b>$misc05: <br>$link</b>
                <br>
                <span>$misc06</span>
            HTML;
            $mail->CharSet = 'UTF-8';
            $mail->MsgHTML($content);
            $mail->Send();
        }

        $this->info(Language::getString("misc07"));
        return $this->redirect("index");
    }

    function resetPassword(string $token)
    {
        $user = $this->db->find(User::class, ["token" => $token]);

        if ($token == __EMPTY__ || $user == null) {
            return $this->error(Language::getString("misc08"));
            return $this->redirect();
        }


        return $this->view(["token" => $token]);
    }

    function resetPasswordSubmit(string $token, string $password, string $confirmPassword)
    {
        Authorization::logOut();

        $user = $this->db->find(User::class, ["token" => $token]);

        if ($token == __EMPTY__ || $password == __EMPTY__ || $confirmPassword == __EMPTY__  || $user == null) {

            $this->error(Language::getString("misc09"));
            return $this->redirect();
        }

        if ($password != $confirmPassword) {

            $this->error(Language::getString("misc10"));
            return $this->redirect();
        }

        $user->salt = Cryptography::computeRandomKey(32);
        $user->password = password_hash($user->salt . $password, PASSWORD_BCRYPT);
        $user->token = password_hash($user->salt . $user->email, PASSWORD_BCRYPT);

        $this->db->save($user);
        $this->db->commit($user);

        $this->success(Language::getString("misc11"));

        return $this->redirect();
    }
}
