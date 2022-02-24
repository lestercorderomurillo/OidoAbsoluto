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
use Cosmic\Bundle\Common\Language;
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
use ZipArchive;

class UserController extends Controller
{
    const usersPerPage = 8;

    private Database $db;

    function __construct(SQLDatabase $db)
    {
        $this->db = $db;
    }

    function encodeTokenFromPianoTest($pianoTest): string
    {
        return Transport::encodeBase64SafeURL(Authorization::getCurrentSecretKey() . $pianoTest->id . Authorization::getCurrentSecretKey() . $pianoTest->try);
    }

    function getUserFromPianoToken(string $token): string
    {
        $decoded = Transport::decodeBase64SafeURL($token);
        $parts = explode(Authorization::getCurrentSecretKey(), $decoded);

        if (!isset($parts[1]) || !isset($parts[2])) {
            return null;
        }

        $user = $this->db->find(User::class, ["id" => $parts[1]]);

        if ($user != null) {
            return $user->token;
        }

        return __EMPTY__;
    }


    function getDetailedTestFromToken(string $token = __EMPTY__)
    {
        if ($token != __EMPTY__) {

            $decoded = Transport::decodeBase64SafeURL($token);
            $parts = explode(Authorization::getCurrentSecretKey(), $decoded);

            if (!isset($parts[1]) || !isset($parts[2])) {
                return null;
            }

            $pianoTest = $this->db->find(PianoTest::class, ["id" => $parts[1], "try" => $parts[2]]);

            if ($pianoTest !== null) {

                $model = new DetailedTestViewModel();
                $model->token = $token;

                $userInfo = $this->db->find(UserInfo::class, ["id" => $parts[1]]);
                $notes = $this->db->findAll(PianoNote::class, ["id" => $parts[1], "try" => $parts[2]]);

                if ($notes !== []) {

                    $model->setValues($pianoTest->getValues());

                    $model->displayString = ($pianoTest->mode == "Full") ? Language::getString("misc12") : Language::getString("misc13");
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

        $questions = Collection::from(new File("app/Views/User/questions." . Language::getLanguage() . ".json"));

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

            $this->error(Language::getString("misc14"));
            return $this->redirect("profile");
        }

        if (count($formData) > 0) {

            $answers = [];

            foreach ($formData as $key => $value) {

                if (str_starts_with($key, "q-")) {

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
            $this->warning(Language::getString("misc15"));
            return $this->redirect("login");
        }

        $audiosSourcesPiano = FileSystem::URLFind(new Folder("app/Content/Audio/Piano/"), "mp3");
        shuffle($audiosSourcesPiano);
        $audiosSourcesPiano = array_slice($audiosSourcesPiano, 0, 30);

        $audiosSourcesSin = FileSystem::URLFind(new Folder("app/Content/Audio/Sin/"), "mp3");
        shuffle($audiosSourcesSin);
        $audiosSourcesSin = array_slice($audiosSourcesSin, 0, 30);

        $audiosSources = Collection::mergeList($audiosSourcesPiano, $audiosSourcesSin);
        $displayString = ($displayMode == "Full") ? Language::getString("misc12") : Language::getString("misc13");

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
            $pianoNote->selectedNote = ($note["selectedNote"] == Language::getString("misc16")) ? "X" : str_replace("X", "#", $note["selectedNote"]);
            $pianoNote->reactionTime = (float)$note["reactionTime"];

            $pianoNotes[] = $pianoNote;
        }

        $this->db->save($pianoTest, true);
        $this->db->save($pianoNotes);
        $this->db->commit();

        return $this->redirect("overview?token=" . $this->encodeTokenFromPianoTest($pianoTest));
    }

    function overview(string $testToken = __EMPTY__)
    {
        if ($testToken == __EMPTY__) {
            $this->error(Language::getString("misc17"));
            return $this->redirect("index");
        }

        $isAdmin = (Authorization::getCurrentRole() === Authorization::ADMIN);
        $isOwner = ($this->db->find(User::class, ["token" => $this->getUserFromPianoToken($testToken)]) != null);

        if (!$isAdmin && !$isOwner) {
            $this->error(Language::getString("misc18"));
            return $this->redirect("index");
        }

        $detailedModel = $this->getDetailedTestFromToken($testToken);

        if ($detailedModel === null) {
            $this->error(Language::getString("misc19"));
            return $this->redirect("index");
        }

        return $this->view($detailedModel);
    }

    function roleChange(int $to, string $userToken)
    {
        $user = $this->db->find(User::class, ["token" => $userToken]);
        $user->role = $to;

        if ($user->role == Authorization::ADMIN || $user->role == Authorization::USER) {
            $this->db->save($user);
            $this->db->commit();
            $this->success(Language::getString("misc20"));
            return $this->redirect("lookup?userToken=$userToken");
        }

        $this->error(Language::getString("misc21"));
        return $this->redirect("lookup?userToken=$userToken");
    }

    function lookup(string $userToken)
    {
        $user = $this->db->find(User::class, ["token" => $userToken]);

        if (!$user == null) {

            $userRole = $user->role;
            $userInfo = $this->db->find(UserInfo::class, ["id" => $user->id]);
            $userInfo->gender = ($userInfo->gender === "M") ? Language::getString("male") : Language::getString("female");
            $tests = $this->db->findAll(PianoTest::class, ["id" => $user->id]);

            $testViewModels = [];

            foreach ($tests as $test) {
                $testViewModel = new PianoTestViewModel();
                $testViewModel->displayMode = ($test->mode == "Full") ? Language::getString("misc12") : Language::getString("misc13");
                $testViewModel->token = $this->encodeTokenFromPianoTest($test);

                $testViewModel->setValues($test->getValues());
                $testViewModels[] = $testViewModel;
            }

            return $this->view(
                [
                    "adminRoleId" => Authorization::ADMIN,
                    "userRoleId" => Authorization::USER,
                    "userToken" => $userToken,
                    "userRole" => $userRole,
                    "host" => __HOST__,
                    "userInfo" => $userInfo,
                    "tests" => $testViewModels,
                    "noTests" => (count($testViewModels) == 0) ? "true" : "false",
                    "disableSurvey" => (!($this->db->exists(Answer::class, ["id" => $userInfo->id]))) ? "true" : "false"
                ]
            );
        }

        return $this->redirect();
    }

    function profile(int $page = 0)
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
                $testViewModel->displayMode = ($test->mode == "Full") ? Language::getString("misc12") : Language::getString("misc13");
                $testViewModel->token = $this->encodeTokenFromPianoTest($test);

                $testViewModel->setValues($test->getValues());
                $testViewModels[] = $testViewModel;
            }

            return $this->view(
                [
                    "host" => __HOST__,
                    "username" =>  $userInfo->firstName . " " . $userInfo->lastName,
                    "tests" => $testViewModels,
                    "noTests" => (count($testViewModels) == 0) ? "true" : "false",
                ]
            );
        } else if (Authorization::getCurrentRole() == Authorization::ADMIN) {

            $adminInfo = $this->db->find(UserInfo::class, ["id" => Authorization::getCurrentId()]);

            $limit = self::usersPerPage + 1;
            $offset = $page * self::usersPerPage;

            $users = $this->db->findAll(User::class, [], "WHERE NOT id='$adminInfo->id' LIMIT $limit OFFSET $offset");
            $usersInfo = $this->db->findAll(UserInfo::class, [], "WHERE NOT id='$adminInfo->id' LIMIT $limit OFFSET $offset");

            $isLastPage = (count($users) <= self::usersPerPage) ? "true" : "false";

            $users = array_slice($users, 0, self::usersPerPage);
            $usersInfo = array_slice($usersInfo, 0, self::usersPerPage);

            $usersCount = count($users);

            $usersSummary = [];

            for ($count = 0; $count < $usersCount; $count++) {

                $userSummary = new UserSummaryViewModel();
                $userSummary->setValues($users[$count]->getValues());
                $userSummary->setValues($usersInfo[$count]->getValues());
                $pianoTests = $this->db->findAll(PianoTest::class, ["id" => $users[$count]->id]);
                $userSummary->tries = count($pianoTests);
                $usersSummary[] = $userSummary;
            }

            return $this->view(
                [
                    "page" => $page,
                    "isFirstPage" => ($page == 0) ? "true" : "false",
                    "isLastPage" => $isLastPage,
                    "host" => __HOST__,
                    "username" =>  $adminInfo->firstName . " " . $adminInfo->lastName,
                    "users" => $usersSummary
                ]
            );
        }
    }

    function exportUserTests(string $userToken)
    {
        $user = $this->db->find(User::class, ["token" => $userToken]);

        if ($user != null) {

            $tests = $this->db->findAll(PianoTest::class, ["id" => $user->id]);

            if ($tests != []) {

                $xlsx = [];

                $zipName = "Pruebas.zip";
                $zip = new ZipArchive();
                $zip->open($zipName, ZipArchive::CREATE);

                $counter = 0;

                foreach ($tests as $test) {

                    $testToken = $this->encodeTokenFromPianoTest($test);
                    $xlsx[] = $this->exportTest($testToken, true);

                    $zip->addFromString('Prueba-' . $counter . '.xlsx', $xlsx[$counter++]);
                }

                $zip->close();
                $this->download($zipName, "application/zip", __EMPTY__, true);
            }
        }

        $this->error(Language::getString("misc22"));
        return $this->redirect();
    }

    function exportSurvey(string $userToken)
    {
        $user = $this->db->find(User::class, ["token" => $userToken]);

        if ($user == null) {
            $this->error(Language::getString("misc23"));
            $this->redirect();
        }

        $userInfo = $this->db->find(UserInfo::class, ["id" => $user->id]);
        $surveyBook = $this->generateSurveyBook($user->id);

        if ($surveyBook == null || $userInfo == null) {
            $this->error(Language::getString("misc24"));
            $this->redirect();
        }

        $books = [
            ["*", Language::getString("misc25") . " " . $userInfo->firstName . " " . $userInfo->lastName],
            ["*", Language::getString("birthDay") . " " . $userInfo->birthDay],
            ["*", Language::getString("country") . " " . $userInfo->country],
            ["*", Language::getString("phone") . " " . $userInfo->phone],
            ["*", Language::getString("lookup012") . " " . $userInfo->gender],
            [],
        ];

        $xlsx = SimpleXLSXGen::fromArray(Collection::mergeList($books, $surveyBook));
        $this->download("Cuestionario.xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", $xlsx);
    }

    function exportTest(string $testToken, bool $returnExcel = false)
    {
        $isAdmin = (Authorization::getCurrentRole() === Authorization::ADMIN);
        $isOwner = ($this->db->find(User::class, ["token" => $this->getUserFromPianoToken($testToken)]) != null);

        if (!$isAdmin && !$isOwner) {
            $this->error(Language::getString("misc26"));
            return $this->redirect("index");
        }

        $test = $this->getDetailedTestFromToken($testToken);

        if ($test != null) {

            $books = [
                ["*", Language::getString("misc27") . "$test->author"],
                ["*", Language::getString("misc28") . "$test->try"],
                ["*", Language::getString("misc29") . "$test->uploadDate"],
                ["*", Language::getString("misc30") . "$test->displayString"],
                ["*", Language::getString("misc31") . "$test->totalTime"],
                [],
                ['#', Language::getString("misc32"), Language::getString("misc33"), Language::getString("misc34"), Language::getString("misc35"), Language::getString("misc36"), Language::getString("misc37"), Language::getString("misc38")]
            ];

            foreach ($test->notes as $note) {

                $matches = null;
                preg_match_all('/[0-9]+/', $note->expectedNote, $matches);

                $octave = $matches[0][0];

                $expectedNotewithoutOctave = preg_replace('/[0-9]+/', '', $note->expectedNote);

                $expectedType = Text::contains($note->expectedNote, "#") ? Language::getString("misc39") : Language::getString("misc40");
                $selectedType = Text::contains($note->selectedNote, "#") ? Language::getString("misc39") : Language::getString("misc40");
                $kind = (($note->noteIndex + 1) < 30) ? "Piano" : "Puro";

                $books[] = [$note->noteIndex + 1, $expectedNotewithoutOctave, $note->selectedNote,  $note->reactionTime, $octave, $expectedType, $selectedType, $kind];
            }

            $books[] = [];

            $surveyBook = $this->generateSurveyBook($test->id);

            if ($surveyBook != null) {
                $books = Collection::mergeList($books, $surveyBook);
            }

            $xlsx = SimpleXLSXGen::fromArray($books);

            if ($returnExcel) {
                return $xlsx;
            }

            $this->download("Prueba.xlsx", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", $xlsx);
        }
    }

    function generateSurveyBook(string $id)
    {
        $answers = $this->db->findAll(Answer::class, ["id" => $id]);

        if ($answers == []) {
            return null;
        }

        $books = [];
        $books[] = ['#', Language::getString("misc41"), Language::getString("misc42")];
        $lang = Language::getLanguage();
        $questions = Collection::from(new File("app/Views/User/questions.$lang.json"));
        $questionsCount = count($questions);

        for ($count = 0; $count < $questionsCount; $count++) {
            $parsedAnswer = Language::getString("misc43");

            foreach ($answers as $answer) {
                if ($answer->question == ($count + 1)) {
                    $parsedAnswer = (strlen($answer->value) > 0) ? $answer->value : Language::getString("misc43");
                }
            }

            $books[] = [($count + 1), $questions[$count]["subject"], $parsedAnswer];
        }

        return $books;
    }
}
