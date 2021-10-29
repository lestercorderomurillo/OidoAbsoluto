<?php

namespace App\Controllers;

use Pipeline\Controller\Controller;
use Pipeline\Core\Types\JSON;
use Pipeline\Database\DatabaseBase;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\Path;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\Utilities\Vector;
use function Pipeline\Kernel\dependency;
use function Pipeline\Kernel\session;

class UserController extends Controller
{
    private DatabaseBase $db;

    function __construct()
    {
        $this->db = dependency("Db");
    }

    function hearingTest(string $mode)
    {
        if ($mode != "simple" && $mode != "full") {

            session("alert-type", "warning");
            session("alert-text", "No se supone que pueda acceder al piano directamente, sino que debe seleccionar su tipo primero.");

            return $this->redirect("login");
        }

        $audios_sources = FileSystem::findWebPaths(new DirectoryPath(SystemPath::WEB, "audio/"), "mp3");
        $audios_sources_json = JSON::create($audios_sources)->toString();

        $output = ["mode" => $mode, "audios_sources" => $audios_sources_json];

        $test_type = "Piano Interactivo";
        if ($mode == "simple") {
            $test_type = "Teclado Interactivo";
            $output["showKeyText"] = true;
        } else {
            $output["showKeyBinds"] = true;
        }

        $output = Vector::mergeNamedValues($output, ["title" => $test_type]);

        return $this->view("hearing", $output);
    }

    function submitHearingTest(string $mode, string $expected_notes,  string $selected_notes)
    {
        $debug1 = var_export($expected_notes, true);
        $debug2 = var_export($selected_notes, true);

        return "Not Implemented $mode $debug1 $debug2";
    }

    function questionsTest()
    {
        $json = json_decode(FileSystem::includeAsString(new Path(SystemPath::VIEWS, "User/questions", "json")), true);

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
