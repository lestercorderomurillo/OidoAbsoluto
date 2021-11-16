<?php

namespace App\Controllers;

use Pipeline\Core\Boot\Controllers\Controller;

class AboutController extends Controller
{
    function contact()
    {
        return $this->view("contact");
    }

    function policy()
    {
        return $this->view("policy");
    }
}