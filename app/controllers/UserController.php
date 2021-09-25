<?php

namespace App\Controllers;

use Pipeline\Controller\Controller;
use Pipeline\Core\Types\JSON;
use Pipeline\Database\AbstractDatabase;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use function Pipeline\Accessors\Dependency;
use function Pipeline\Accessors\Session;

class UserController extends Controller
{
    private AbstractDatabase $db;

    function __construct(){
        $this->db = Dependency("Db");
    }

    function hearingTest(string $mode)
    {
        if ($mode != "simple" && $mode != "full") {

            Session("message-type", "warning");
            Session("message", "No se supone que pueda acceder al piano directamente, sino que debe seleccionar su tipo primero.");

            return $this->redirect("login");
        }

        $audios_sources = FileSystem::findWebPaths(new DirectoryPath(SystemPath::WEB, "audio/"), "mp3");
        $audios_sources_json = JSON::create($audios_sources)->toJavascriptString();

        $test_type = "Piano Interactivo";
        if ($mode == "simple") {
            $test_type = "Teclado Interactivo";
        }

        return $this->view("hearing", ["mode" => $mode, "audios_sources" => $audios_sources_json, "title" => $test_type]);
    }

    function submitHearingTest(string $mode, string $expected_notes,  string $selected_notes)
    {
        $debug1 = var_export($expected_notes, true);
        $debug2 = var_export($selected_notes, true);

        return "Not Implemented $mode $debug1 $debug2";
    }

    function overview()
    {
        return $this->view("overview");
    }

    function profile()
    {
        return $this->view("profile");
    }
}
