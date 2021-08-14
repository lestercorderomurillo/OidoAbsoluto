<?php

$framework_vendor = "VIP";

createTemplateFile(__DIR__."/app/test");


function createController(string $controller_name string $vendor){
    $controller_name .= "Controller";
    createTemplateFile(__DIR__."/app/controllers/$controller_name.php", );
}

function createTemplateFile(
    string $path,
    string $vendor = "Vendor",
    string $scope = "Scope",
    string $class_name = "SampleClass",
    string $function_name = "__construct()",
    string $function_content = "echo(\"Hello World\");"
) {
    $file = fopen("$path.php", "w") or die("Unable to open file!");

    $data = <<<TEXT
    <?php

    namespace $vendor\\$scope;
    
    class $class_name 
    {
        public function $function_name()
        {
           $function_content
        }
    }
    TEXT;

    fwrite($file, $data);
    fclose($file);
}
