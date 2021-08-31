<?php

namespace App\Controllers;

use Pipeline\Controller\Controller;
use Pipeline\Database\AbstractDatabase;
use function Pipeline\Accessors\Dependency;

class AdminController extends Controller
{
    private AbstractDatabase $db;

    function __construct(){
        $this->db = Dependency("Db");
    }

    function overview()
    {
        return $this->view("overview");
    }
}
