<?php

namespace App\Controllers;

use Pipeline\Controller\Controller;

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