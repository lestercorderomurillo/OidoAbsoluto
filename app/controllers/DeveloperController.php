<?php

namespace App\Controllers;

use Pipeline\Controller\Controller;
use Pipeline\Database\AbstractDatabase;
use Pipeline\PypeEngine\PypeCompiler;
use Pipeline\PypeEngine\PypeComponent;
use Pipeline\PypeEngine\PypeTemplate;

use function Pipeline\Accessors\Dependency;

class DeveloperController extends Controller
{
    //private AbstractDatabase $db;
    //private PypeCompiler $experimental;

    function __construct()
    {
        $this->db = Dependency("Db");
        $this->experimental = Dependency(PypeCompiler::class);
    }

    function testMethod1()
    {
        return $this->view(
            "pypetest",
            [
                "dogs" => 
                [
                    [
                        "name" => "Kiba",
                        "age" => 10
                    ],
                    [
                        "name" => "Killua",
                        "age" => 23
                    ]
                ]
                
            ]
        );
    }

    function testMethod2()
    {

        /*$models = $this->db->findAll(UserInfo::class, ["gender" => "M"]);
        $out = var_export($models, true);
        $model = $this->db->find(UserInfo::class, ["id" => "1", "gender" => "M"]);
        $out .= "<br>" . var_export($model, true);*/
        /*return $out;*/
    }
}
