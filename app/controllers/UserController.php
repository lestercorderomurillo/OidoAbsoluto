<?php

namespace App\Controllers;

use App\Models\Answer;
use App\Models\UserInfo;
use Cosmic\Binder\Authorization;
use Cosmic\Core\Bootstrap\Controller;
use Cosmic\Core\Types\JSON;
use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\Folder;
use Cosmic\FileSystem\Paths\File;
use Cosmic\HTTP\Request;
use Cosmic\ORM\Bootstrap\Database;
use Cosmic\ORM\Databases\SQL\SQLDatabase;
use Cosmic\Utilities\Collection;
use Cosmic\Utilities\Text;

class UserController extends Controller
{
    private Database $db;

    function __construct(SQLDatabase $db)
    {
        $this->db = $db;
    }

    function survey()
    {
        if ($this->db->exists(Answer::class, ["id" => Authorization::getCurrentId()])) {
            return $this->redirect("profile");
        }

        $userInfo = $this->db->find(UserInfo::class, ["id" => Authorization::getCurrentId()]);
        $questions = Collection::from(new File("app/Views/User/questions.json"));

        $fixedQuestions = [];

        foreach ($questions as $question) {
            if (isset($question['gender'])) {
                if ($question['gender'] == $userInfo->gender) {
                    $fixedQuestions[] = $question;
                }
            } else {
                $fixedQuestions[] = $question;
            }
        }

        return $this->view(["gender" => $userInfo->gender, "surveyQuestions" => $fixedQuestions]);
    }

    function surveySubmit(Request $request)
    {
        $formData = $request->getFormData();

        if ($this->db->exists(Answer::class, ["id" => Authorization::getCurrentId()])) {

            $this->danger("El formulario solo puede ser subido al servidor una única vez por usuario.");
            return $this->redirect("profile");
        }

        if (count($formData) > 0) {

            $answers = [];

            foreach ($formData as $key => $value) {

                if (Text::startsWith($key, "q-")) {

                    $question = (int)str_replace("q-", __EMPTY__, $key);
                    $answer = new Answer();
                    $answer->setId(Authorization::getCurrentId());
                    $answer->question = $question;
                    $answer->value = $value;
                    $answers[] = $answer;
                }
            }

            $this->db->save($answers);
            $this->db->commit();
        }

        return $this->redirect("profile");
    }

    function piano(string $displayMode)
    {
        if (!Text::equals($displayMode, ["Simple", "Full"])) {
            $this->warning("No se supone que pueda acceder al piano directamente, sino que debe seleccionar su tipo primero.");
            return $this->redirect("login");
        }

        $audiosSources = FileSystem::URLFind(new Folder("app/Content/audio/"), "mp3");
        $displayString = ($displayMode == "Full") ?  "Piano Interactivo" : "Teclado Interactivo";

        return $this->view(["displayMode" => $displayMode, "audiosSources" => $audiosSources, "displayString" => $displayString]);
    }


    function pianoSubmit(Request $request)
    {
        var_export($request);

        die();

        //return "Not Implemented $mode $debug1 $debug2";
    }


    // WIP
    function profile()
    {

        if (!$this->db->exists(Answer::class, ["id" => Authorization::getCurrentId()])) {
            return $this->redirect("survey");
        }

        $singleTest = [

            "try" => 1,
            "date" => "08/20/2021 21:04:23",

            "totalMatches" => 60,
            "totalNotes" => 60,

            "totalPianoMatches" => 30,
            "totalPianoNotes" => 30,

            "totalSinMatches" => 30,
            "totalSinNotes" => 30,

            "totalNaturalMatches" => 17,
            "totalNaturalNotes" => 17,

            "total#Matches" => 13,
            "total#Notes" => 13,

            "CMatches" => 4,
            "CTotal" => 4,

            "C#Matches" => 4,
            "C#Total" => 4,

            "DMatches" => 4,
            "DTotal" => 4,

            "D#Matches" => 4,
            "D#Total" => 4,

            "EMatches" => 4,
            "ETotal" => 4,

            "FMatches" => 4,
            "FTotal" => 4,

            "F#Matches" => 4,
            "F#Total" => 4,

            "GMatches" => 4,
            "GTotal" => 4,

            "G#Matches" => 4,
            "G#Total" => 4,

            "AMatches" => 4,
            "ATotal" => 4,

            "A#Matches" => 4,
            "A#Total" => 4,

            "BMatches" => 4,
            "BTotal" => 4,
        ];


        $tests = [$singleTest];

        /** @var UserInfo $model */
        $model = $this->db->find(UserInfo::class, ["id" => Authorization::getCurrentId()]);

        return $this->view([
            "username" => $model->firstName . " " . $model->lastName,
            "tests" => $tests
        ]);
    }


    function hearingTest(string $mode)
    {
        if ($mode != "simple" && $mode != "full") {

            $this->warning("No se supone que pueda acceder al piano directamente, sino que debe seleccionar su tipo primero.");

            return $this->redirect("login");
        }

        $audiosSources = FileSystem::URLFind(new Folder("App/Content/audio/"), "mp3");
        $audiosSourcesJSON = JSON::create($audiosSources);

        $output = ["mode" => $mode, "audiosSources" => $audiosSourcesJSON];

        $test_type = "Piano Interactivo";

        if ($mode == "simple") {
            $test_type = "Teclado Interactivo";
            $output["showKeyText"] = true;
        } else {
            $output["showKeyBinds"] = true;
        }

        $output = Collection::mergeDictionary(true, $output, ["title" => $test_type]);

        return $this->view("hearing", $output);
    }

    function testResult(int $id)
    {
        return $this->view("result");
    }
}
