<?php

namespace App\Controllers;

use VIP\Controller\BaseController;
use VIP\FileSystem\BasePath;
use VIP\FileSystem\FileSystem;
use VIP\FileSystem\DirectoryPath;
use VIP\HTTP\Server\Response\JSON;
use VIP\HTTP\Server\Response\View;
use VIP\HTTP\Server\Response\Redirect;

use function VIP\Core\Session;

class PianoController extends BaseController
{
    function hearingTest(string $mode)
    {
        if ($mode != "simple" && $mode != "full") {
            Session()->store("message-type", "warning");
            Session()->store("message", "No se supone que pueda acceder al piano directamente, sino que debe seleccionar su tipo primero.");
            return new Redirect("login");
        }

        $audios_sources = FileSystem::findRoutesFromInternal(new DirectoryPath(BasePath::DIR_WEB, "audio/"), "mp3");
        $audios_sources_json = (new JSON($audios_sources))->toJavascriptString();

        return new View("piano", ["mode" => $mode, "audios_sources" => $audios_sources_json]);
    }

    function submitHearingTest(string $mode, string $expected_notes,  string $selected_notes)
    {
        $debug1 = var_export($expected_notes, true);
        $debug2 = var_export($selected_notes, true);
        return "Not Implemented $mode $debug1 $debug2";
    }
}
