<?php

namespace App\Controllers;

use Cosmic\Bundle\Middlewares\Authentication;
use Cosmic\Core\Controllers\Controller;
use Cosmic\Core\Types\JSON;
use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\Folder;
use Cosmic\FileSystem\Paths\File;
use Cosmic\Utilities\Collection;

class UserController extends Controller
{
    function profile()
    {
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

        return $this->view([
            "username" => Authentication::getCurrentUsername(),
            "tests" => $tests
        ]);
    }

    function logout()
    {
        Authentication::logout();
        return $this->redirect();
    }










    // WIP
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

    function submitHearingTest(string $mode, string $expectedNotes,  string $selectedNotes)
    {
        $debug1 = var_export($expectedNotes, true);
        $debug2 = var_export($selectedNotes, true);

        return "Not Implemented $mode $debug1 $debug2";
    }

    function questionsTest()
    {
        $json = Collection::from(new File("app/Views/User/questions.json"));
        return $this->view("questions", ["questions" => $json]);
    }

    function testResult(int $id)
    {
        return $this->view("result");
    }
}
