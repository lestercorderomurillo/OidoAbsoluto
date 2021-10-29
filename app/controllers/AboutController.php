<?php

namespace App\Controllers;

use Pipeline\Controller\ControllerBase;

class AboutController extends ControllerBase
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