<?php

namespace App\Controllers;

use Pipeline\Controller\Controller;
use Pipeline\Core\Types\JSON;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\BasePath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;

use function Pipeline\Accessors\Session;

class PianoController extends Controller
{
    function hearingTest(string $mode)
    {
        if ($mode != "simple" && $mode != "full") {

            Session("message-type", "warning");
            Session("message", "No se supone que pueda acceder al piano directamente, sino que debe seleccionar su tipo primero.");

            return $this->redirect("login");
        }

        $audios_sources = FileSystem::findRoutesFromInternal(new DirectoryPath(BasePath::DIR_WEB, "audio/"), "mp3");
        $audios_sources_json = (new JSON($audios_sources))->toJavascriptString();

        return $this->view("piano", ["mode" => $mode, "audios_sources" => $audios_sources_json]);
    }

    function submitHearingTest(string $mode, string $expected_notes,  string $selected_notes)
    {
        $debug1 = var_export($expected_notes, true);
        $debug2 = var_export($selected_notes, true);
        
        return "Not Implemented $mode $debug1 $debug2";
    }
}
