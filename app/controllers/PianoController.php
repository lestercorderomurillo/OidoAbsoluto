<?php

namespace App\Controllers;

use VIP\Core\BaseController;
use VIP\FileSystem\FileSystem;
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

        $audiosSources = (new JSON(FileSystem::findWebExposed("audio/", "mp3")))->toString();

        return new View("piano", ["mode" => $mode, "audiosSources" => $audiosSources]);
    }

    function submitHearingTest(string $mode, string $expected_notes,  string $selected_notes)
    {
        $debug1 = var_export($expected_notes, true);
        $debug2 = var_export($selected_notes, true);
        return "Not Implemented $mode $debug1 $debug2";
    }
}
