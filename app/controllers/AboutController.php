<?php

namespace App\Controllers;

use Pipeline\Core\Boot\Controllers\Controller;

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