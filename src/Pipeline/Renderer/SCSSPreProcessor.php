<?php

namespace Pipeline\Renderer;

use Pipeline\App\App;
use Pipeline\FileSystem\Path\BasePath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Web\WebDirectoryPath;
use ScssPhp\ScssPhp\Compiler as SCSSCompiler;
use function Pipeline\Accessors\Configuration;

class SCSSPreProcessor
{
    public function compileProjectStylesheets()
    {
        if (Configuration("development.compileSCSS")) {

            $page_scss_folder = (new WebDirectoryPath())->toString();
            $prefab_scss_folder = (new DirectoryPath(BasePath::DIR_INCLUDE, "public/"))->toWebDirectoryPath()->toString();
            $scss_folders = [$prefab_scss_folder, $page_scss_folder];
            $output_folder = (new WebDirectoryPath("web/css/"))->toString();
            $scss_compiler = new SCSSCompiler();

            $scss_compiler->setImportPaths($scss_folders);

            if (!file_exists($output_folder)) {
                mkdir($output_folder, 0777, true);
            }

            for ($i = 0; $i < count($scss_folders); $i++) {

                $files = glob($scss_folders[$i] . "*.scss");
                foreach ($files as $file_path) {
                    $file_path_elements = pathinfo($file_path);
                    $file_name = $file_path_elements['filename'];
                    $string_scss = file_get_contents($scss_folders[$i] . $file_name . ".scss");
                    $string_css = $scss_compiler->compile($string_scss);
                    $output = $output_folder . $file_name . ".css";
                    file_put_contents($output, $string_css);
                }
            }
        }
    }
}
