<?php 

namespace App\Controllers;

use Pipeline\HTTP\Server\Response\View;

class TestController 
{
    public function actionMethod()
    {
        return new View("view_name");
    }
}