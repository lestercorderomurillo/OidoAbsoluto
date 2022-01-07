<?php

namespace App\Controllers;

use Cosmic\Core\Types\JSON;
use Cosmic\Core\Bootstrap\Controller;
use Cosmic\ORM\Databases\SQL\SQLDatabase;

class DeveloperController extends Controller
{
    private SQLDatabase $db;

    function __construct(SQLDatabase $db)
    {
        $this->db = $db;
    }

    function index()
    {
        return ["test" => "good"];
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
        $model = $this->db->find(UserInfo::class, ["id" => "1", "gender" => "M"]);

        return $this->JSON($model);
    }

    /*
    function requestMatched(Request $request){

    }

    function modelMatched(Model $model){

    }

    function valueMatched(string $a, string $b){

    }*/
}
