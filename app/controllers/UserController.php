<?php

namespace App\Controllers;

use App\Models\Answer;
use App\Models\PianoNote;
use App\Models\PianoTest;
use App\Models\UserInfo;
use App\ViewModels\PianoTestViewModel;
use App\ViewModels\DetailedTestViewModel;
use Cosmic\HTTP\Request;
use Cosmic\Binder\Authorization;
use Cosmic\Core\Bootstrap\Controller;
use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\Folder;
use Cosmic\FileSystem\Paths\File;
use Cosmic\ORM\Bootstrap\Database;
use Cosmic\ORM\Databases\SQL\SQLDatabase;
use Cosmic\Utilities\Collection;
use Cosmic\Utilities\Text;
use Cosmic\Utilities\Transport;

class UserController extends Controller
{
    private Database $db;

    function __construct(SQLDatabase $db)
    {
        $this->db = $db;
    }

    function createTokenFromPianoTest($pianoTest): string
    {
        return Transport::encodeBase64SafeURL($pianoTest->id . "-x-" . $pianoTest->try);
    }

    function getDetailedTestFromToken(string $token = __EMPTY__)
    {
        if ($token != __EMPTY__) {

            $decoded = Transport::decodeBase64SafeURL($token);
            $parts = explode("-x-", $decoded);

            if (!isset($parts[0]) || !isset($parts[1])) {
                return null;
            }

            $pianoTest = $this->db->find(PianoTest::class, ["id" => $parts[0], "try" => $parts[1]]);

            if ($pianoTest !== null) {

                $model = new DetailedTestViewModel();

                $userInfo = $this->db->find(UserInfo::class, ["id" => $parts[0]]);
                $notes = $this->db->findAll(PianoNote::class, ["id" => $parts[0], "try" => $parts[1]]);

                if ($notes !== []) {

                    $model->setValues($pianoTest->getValues());

                    $model->displayString = ($pianoTest->mode == "Full") ?  "Piano Interactivo" : "Teclado Interactivo";
                    $model->author = $userInfo->firstName . " " . $userInfo->lastName;
                    $model->notes = $notes;
                    $model->totalNotes = 60;
                    $model->totalPianoNotes = 30;
                    $model->totalSinNotes = 30;

                    $counter = 0;

                    foreach ($notes as $note) {

                        $expectedNotewithoutOctave = preg_replace('/[0-9]+/', '', $note->expectedNote);

                        $isNatural = false;

                        if (!Text::contains($expectedNotewithoutOctave, "#")) {

                            $model->totalNaturalNotes++;
                            $isNatural = true;
                        } else {

                            $model->totalSosNotes++;
                        }

                        if ($note->selectedNote == $expectedNotewithoutOctave) {
                            $model->totalMatches++;
                            if ($counter < $model->totalPianoNotes) {
                                $model->totalPianoMatches++;
                            } else {
                                $model->totalSinMatches++;
                            }

                            if ($isNatural) {
                                $model->totalNaturalMatches++;
                            } else {
                                $model->totalSosMatches++;
                            }
                        }

                        $counter++;
                    }

                    return $model;
                }
            }
        }

        return null;
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

            $this->error("El formulario solo puede ser subido al servidor una única vez por usuario.");
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
        $formData = $request->getFormData();

        $totalTime = $formData['totalTime'];
        $mode = $formData['mode'];

        $notes = $formData['notes'];

        $try = 1;

        $tests = $this->db->findAll(PianoTest::class, ["id" => Authorization::getCurrentId()], "ORDER BY try DESC LIMIT 0, 1");

        if ($tests != []) {
            $lastestTest = $tests[0];
            $try = $lastestTest->try + 1;
        }

        $pianoTest = new PianoTest();
        $pianoTest->setId(Authorization::getCurrentId());
        $pianoTest->try = $try;
        $pianoTest->uploadDate = date('Y-m-d H:i:s');
        $pianoTest->totalTime = $totalTime;
        $pianoTest->mode = $mode;

        $pianoNotes = [];

        foreach ($notes as $noteIndex => $note) {

            $pianoNote = new PianoNote();

            $pianoNote->setId(Authorization::getCurrentId());
            $pianoNote->try = $try;
            $pianoNote->noteIndex = $noteIndex;
            $pianoNote->expectedNote = $note["expectedNote"];
            $pianoNote->selectedNote = ($note["selectedNote"] == "No se seleccionó ninguna nota") ? "-" : str_replace("X", "#", $note["selectedNote"]);
            $pianoNote->reactionTime = (float)$note["reactionTime"];

            $pianoNotes[] = $pianoNote;
        }

        $this->db->save($pianoTest);
        $this->db->save($pianoNotes);
        $this->db->commit();

        return $this->redirect("overview?token=" . $this->createTokenFromPianoTest($pianoTest));
    }

    function overview(string $token = __EMPTY__)
    {
        if ($token == __EMPTY__) {
            $this->error("El token especificado no es valido en este contexto.");
            return $this->redirect("index");
        }

        $detailedModel = $this->getDetailedTestFromToken($token);

        if ($detailedModel === null) {
            $this->error("El token proporcionado no corresponde a ninguna prueba en el sistema.");
            return $this->redirect("index");
        }

        return $this->view($detailedModel);
    }

    function profile()
    {
        if (!$this->db->exists(Answer::class, ["id" => Authorization::getCurrentId()])) {
            return $this->redirect("survey");
        }

        $userInfo = $this->db->find(UserInfo::class, ["id" => Authorization::getCurrentId()]);
        $tests = $this->db->findAll(PianoTest::class, ["id" => Authorization::getCurrentId()]);

        $testViewModels = [];

        foreach ($tests as $test) {
            $testViewModel = new PianoTestViewModel();
            $testViewModel->displayMode = ($test->mode == "Full") ?  "Piano Interactivo" : "Teclado Interactivo";
            $testViewModel->token = $this->createTokenFromPianoTest($test);
            
            $testViewModel->setValues($test->getValues());
            $testViewModels[] = $testViewModel;
        }

        return $this->view(
            [
                "host" => __HOST__,
                "username" =>  $userInfo->firstName . " " . $userInfo->lastName,
                "tests" => $testViewModels
            ]
        );
    }
}
