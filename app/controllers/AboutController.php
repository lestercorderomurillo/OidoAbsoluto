<?php

namespace App\Controllers;

use Cosmic\Core\Bootstrap\Controller;

class AboutController extends Controller
{
    function contact()
    {
        return $this->view();
    }

    function policy()
    {
        return $this->view();
    }
}