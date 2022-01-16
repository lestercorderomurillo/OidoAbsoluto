<?php

namespace Cosmic\CLI\Commands;

use Cosmic\CLI\Abstracts\Command;
use Cosmic\FileSystem\FS;
use Cosmic\FileSystem\Paths\FilePath;
use Psr\Log\LogLevel;

class CreateControllerCommand extends Command
{
    const categoryName = "controller";
    const commandName = "create";

    const errors = [
        "Must be a valid controller name."
    ];

    public function verifyArguments(array $args)
    {
        if(strlen($args[0]) < 1){
            $this->error = "Controller name cannot be empty.";
            return false;
        }

        return true;
    }
    
    public function execute(...$args): int
    {
        return $this->call(...$args);
    }

    public function call($controllerName): int
    {

        $className = str_ends_with($controllerName, "Controller") ? $controllerName : $controllerName . "Controller";
        $className = ucfirst($className);
        $controllerName = ucfirst($controllerName);

        $output = <<<PHP
        <?php\n
        namespace App\Controllers;\n
        use Cosmic\Core\Abstracts\Controller;\n
        class $className extends Controller
        {
            public function index()
            {
                return "Hello World";
            }
        }
        PHP;

        $file = new FilePath("app/Controllers/$controllerName" . "Controller.php");

        if(!FS::exists($file)){
            FS::write($file, $output);
            cout("Controller $controllerName has been created successfully.", [], LogLevel::INFO);
        }else{
            cout("Controller already exists or not enough write permission.", [], LogLevel::ERROR);
        }


        return 1;
    }
}
