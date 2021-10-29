<?php

namespace App\Controllers;

use Pipeline\Controller\Controller;
use Pipeline\Core\Types\JSON;
use Pipeline\Database\SQLDatabase;

class DeveloperController extends Controller
{
    private SQLDatabase $db;

    function __construct()
    {
        //$this->db = $db;
    }

    function testMethod1()
    {
        return $this->view([
            "data1" => 1,
            "data2" => "string",
            "data3" => JSON::create(["json_test" => 1]),
            "data4" => [1, 2, 3]
        ]);
    }

    function testMethod2()
    {
        $models = ["1" => "2"];//$this->db->findAll(UserInfo::class, ["gender" => "M"]);
        //$model = $this->db->find(UserInfo::class, ["id" => "1", "gender" => "M"]);
        return $this->JSON($models);
    }
}
