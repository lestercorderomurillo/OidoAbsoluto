<?php

namespace App\Controllers;

use Cosmic\Core\Controllers\Controller;

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