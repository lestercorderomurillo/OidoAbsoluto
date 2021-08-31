<?php

namespace App\Controllers;

use App\Models\UserInfo;
use Pipeline\Controller\Controller;
use Pipeline\Database\AbstractDatabase;

class DeveloperController extends Controller
{
    private AbstractDatabase $db;

    function testMethod1()
    {
        $models = $this->db->findAll(UserInfo::class, ["gender" => "M"]);
        var_dump($models);

        $result = $this->db->find(UserInfo::class, ["id" => "1", "gender" => "M"]);
        var_dump($result->expose());

        return "Test";
    }
}