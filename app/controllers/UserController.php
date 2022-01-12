<?php

namespace App\Controllers;

use App\Models\Answer;
use App\Models\PianoNote;
use App\Models\PianoTest;
use App\Models\User;
use App\Models\UserInfo;
use App\ViewModels\PianoTestViewModel;
use App\ViewModels\DetailedTestViewModel;
use App\ViewModels\UserSummaryViewModel;
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
use Shuchkin\SimpleXLSXGen\SimpleXLSXGen;

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
                $model->token = $token;

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
        if (Authorization::getCurrentRole() == Authorization::ADMIN) {
            return $this->redirect("profile");
        }

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
        if (Authorization::getCurrentRole() == Authorization::ADMIN) {
            return $this->redirect("profile");
        }

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

        $audiosSources = FileSystem::URLFind(new Folder("app/Content/Audio/"), "mp3");
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
            $pianoNote->selectedNote = ($note["selectedNote"] == "No se seleccionó ninguna nota") ? "X" : str_replace("X", "#", $note["selectedNote"]);
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
        if (Authorization::getCurrentRole() == Authorization::USER) {

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

        } else if (Authorization::getCurrentRole() == Authorization::ADMIN) {

            $adminInfo = $this->db->find(UserInfo::class, ["id" => Authorization::getCurrentId()]);

            $users = $this->db->findAll(User::class, [], "WHERE NOT id='$adminInfo->id'");
            $usersInfo = $this->db->findAll(UserInfo::class, [], "WHERE NOT id='$adminInfo->id'");

            $usersCount = count($users);
            $usersSummary = [];

            for($count = 0; $count < $usersCount; $count++){
                $userSummary = new UserSummaryViewModel();
                $userSummary->setValues($users[$count]->getValues());
                $userSummary->setValues($usersInfo[$count]->getValues());
                $usersSummary[] = $userSummary;
            }

            return $this->view(
                [
                    "host" => __HOST__,
                    "username" =>  $adminInfo->firstName . " " . $adminInfo->lastName,
                    "users" => $usersSummary
                ]
            );
        }
    }

    function exportTest(string $token)
    {
        $test = $this->getDetailedTestFromToken($token);

        if ($test != null) {

            $books = [
                ["*", "Nombre completo: $test->author"],
                ["*", "Número de intento: $test->try"],
                ["*", "Fecha de subida: $test->uploadDate"],
                ["*", "Modo de visualización: $test->displayString"],
                ["*", "Tiempo total transcurrido: $test->totalTime"],
                [],
                ['#', 'Nota Reproducida', 'Nota Presionada', 'Tiempo de reacción (ms)', 'Octava Reproducida', 'Tipo Reproducido', 'Tipo Presionado', "Clasificación"]
            ];

            foreach ($test->notes as $note) {

                $matches = null;
                preg_match_all('/[0-9]+/', $note->expectedNote, $matches);

                $octave = $matches[0][0];

                $expectedNotewithoutOctave = preg_replace('/[0-9]+/', '', $note->expectedNote);

                $expectedType = Text::contains($note->expectedNote, "#") ? "Sostenido" : "Natural";
                $selectedType = Text::contains($note->selectedNote, "#") ? "Sostenido" : "Natural";
                $kind = (($note->noteIndex + 1) < 30) ? "Piano" : "Puro";

                $books[] = [$note->noteIndex + 1, $expectedNotewithoutOctave, $note->selectedNote,  $note->reactionTime, $octave, $expectedType, $selectedType, $kind];
            }

            $books[] = [];
            $books[] = ['#', 'Pregunta', 'Respuesta'];

            $questions = Collection::from(new File("app/Views/User/questions.json"));
            $answers = $this->db->findAll(Answer::class, ["id" => $test->id]);

            $questionsCount = count($questions);

            for ($count = 0; $count < $questionsCount; $count++) {
                $parsedAnswer = "Sin respuesta / No aplica";

                foreach ($answers as $answer) {
                    if ($answer->question == ($count + 1)) {
                        $parsedAnswer = $answer->value;
                    }
                }

                $books[] = [($count + 1), $questions[$count]["subject"], $parsedAnswer];
            }

            $xlsx = SimpleXLSXGen::fromArray($books);
            $this->download("Notes_$token.xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", $xlsx);
        }
    }
}
