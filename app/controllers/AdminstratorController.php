<?php

namespace App\Controllers;

use Pipeline\Core\Types\JSON;
use Pipeline\Core\Boot\Controllers\Controller;
use Pipeline\Core\DI;
use Pipeline\Database\Boot\Database;
use Pipeline\Database\SQLDatabase;

class AdminstratorController extends Controller
{
    private Database $db;

    function __construct()
    {
        $this->db = DI::getDependency(SQLDatabase::class);
    }

    function overview()
    {
        $charts = [
            "setup" => [
                [
                    "title" => "Según tipo de nota",
                    "subTitle" => ""
                ],
                [
                    "title" => "Según nota específica",
                    "subTitle" => ""
                ]
            ],
            "data" => [
                [
                    ["label" => "Tonos Puros", "y" => 76, "yMax" => 200, "percent" => round((76 / 200) * 100, 2)],
                    ["label" => "Tonos Sintéticos", "y" => 78, "yMax" => 200, "percent" => round((78 / 200) * 100, 2)],
                    ["label" => "Total", "y" => 66, "yMax" => 266, "percent" => round((66 / 266) * 100, 2)]
                ],
                [
                    ["label" => "C", "y" => 76, "yMax" => 200, "percent" => round((76 / 200) * 100, 2)],
                    ["label" => "C#", "y" => 78, "yMax" => 200, "percent" => round((78 / 200) * 100, 2)],
                    ["label" => "D", "y" => 66, "yMax" => 266, "percent" => round((66 / 266) * 100, 2)],
                    ["label" => "E", "y" => 66, "yMax" => 266, "percent" => round((66 / 266) * 100, 2)],
                    ["label" => "F", "y" => 32, "yMax" => 266, "percent" => round((66 / 266) * 100, 2)],
                    ["label" => "G", "y" => 25, "yMax" => 266, "percent" => round((66 / 266) * 100, 2)],
                    ["label" => "A", "y" => 26, "yMax" => 266, "percent" => round((66 / 266) * 100, 2)],
                    ["label" => "B", "y" => 66, "yMax" => 266, "percent" => round((66 / 266) * 100, 2)]
                ]
            ]
        ];

        $count = count($charts["setup"]);
        $view_data = ["numberOfCharts" => $count - 1];

        for($i = 0; $i < $count; $i++){
            $view_data["id:$i"] = "guid_$i";
            $view_data["title:$i"] = $charts["setup"][$i]["title"];
            $view_data["subTitle:$i"] = $charts["setup"][$i]["subTitle"];
            $view_data["data:$i"] = new JSON($charts["data"][$i]);
        }
        return $this->view("overview", $view_data);
    }
}
