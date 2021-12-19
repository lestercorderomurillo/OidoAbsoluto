<?php

namespace App\Controllers;

use Cosmic\Core\Controllers\Controller;
use Cosmic\Core\Types\JSON;
use Cosmic\Database\Boot\Database;
use Cosmic\Database\SQLDatabase;
use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\Directory;
use Cosmic\FileSystem\Paths\File;
use Cosmic\Utilities\Collection;

class UserController extends Controller
{
    function hearingTest(string $mode)
    {
        if ($mode != "simple" && $mode != "full") {

            $this->warning("No se supone que pueda acceder al piano directamente, sino que debe seleccionar su tipo primero.");

            return $this->redirect("login");
        }

        $audiosSources = FileSystem::URLFind(new Directory("App/Content/audio/"), "mp3");
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

    function profile()
    {
        return $this->view("profile");
    }

    function testResult(int $id)
    {
        return $this->view("result");
    }
}
