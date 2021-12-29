<?php

namespace Cosmic\CLI;

class Terminal
{
    const FRAMEWORK = "Cosmic";

    public function __construct(int $argc, array $argv)
    {
        if ($argc == 1) {
            echo ("To start, please write a command.");
            $this->displayHelp();
        }

        if ($argc > 1) {
            switch (strtolower($argv[1])) {
                case "help":
                    $this->displayHelp();
                    break;

                case "controller":
                    if ($argc > 2) {
                        $this->createController($argv[2], "App");
                    } else {
                        $this->displayInvalidArgsNumberError();
                    }
                    break;

                case "class":
                    if ($argc > 3) {
                        $this->createClass($argv[2], $argv[3], self::FRAMEWORK);
                    } else {
                        $this->displayInvalidArgsNumberError();
                    }
                    break;
                default:
                    $this->displayInvalidArgsNumberError();
                    break;
            }
        }
    }

    public function displayHelp()
    {
        $data = <<<TEXT
        List of all valid CLI commands:
        php cli.pexec help
        php cli.pexec model [name]
        php cli.pexec view [name]
        php cli.pexec controller [name]
        php cli.pexec class [module\submodule\\...] [name]
        \n
        TEXT;
        echo ($data);
    }

    private function displayInvalidArgsNumberError()
    {
        echo ("Error: Invalid number of arguments.");
        $this->displayHelp();
    }

    private function createClass(string $module_path, string $class_name, string $vendor)
    {
        $class_name = ucwords($class_name);
        $module_path = rtrim($module_path, '\/');

        $this->createTemplateFile(
            __DIR__ . "/src/$vendor/$module_path/$class_name",
            $vendor,
            $module_path,
            $class_name,
            "__construct"
        );
    }

    private function createController(string $controller_name, string $vendor)
    {
        $controller_name = ucwords($controller_name . "Controller");

        $this->createTemplateFile(
            dirname(__DIR__, 3) . "/app/Controllers/$controller_name",
            $vendor,
            "Controllers",
            $controller_name,
            "index",
            "return \$this->view();",
            ["Core\Bootstrap\Controller"],
            "extends Controller"
        );
    }

    private function createTemplateFile(
        string $path,
        string $vendor = "Vendor",
        string $scope = "Scope",
        string $class_name = "SampleClass",
        string $function_name = "__construct",
        string $function_content = "echo(\"Hello World\");",
        array $imports = [],
        string $extends = ""
    ) {
        $folder = str_replace("$class_name", "", $path);
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $php_path = "$path.php";


        if (is_file($php_path)) {
            die("File already exists: " . $php_path);
        }

        $file = fopen($php_path, "w") or die("Unable to open file!");

        $vendor = ucwords($vendor);
        $scope = ucwords($scope);
        $class_name = ucwords($class_name);

        $imports_text = "";
        foreach ($imports as $import) {
            $imports_text = "use Cosmic\\$import;\n";
        }

        if ($imports_text != "") {
            $imports_text = "\n" . $imports_text;
        }

        $data = <<<TEXT
        <?php 
        
        namespace $vendor\\$scope;
        $imports_text
        class $class_name $extends
        {
            public function $function_name()
            {
                $function_content
            }
        }
        TEXT;

        fwrite($file, "" . $data);
        fclose($file);
        echo ("$class_name has been created successfully.");
    }
}
