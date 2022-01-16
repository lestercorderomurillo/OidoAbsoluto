<?php

namespace App\Controllers;

use Cosmic\Core\Abstracts\Controller;

class InformationController extends Controller
{
    function about()
    {
        return $this->view();
    }

    function policy()
    {
        return $this->view();
    }
}