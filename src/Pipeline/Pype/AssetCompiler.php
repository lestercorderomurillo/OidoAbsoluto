<?php

namespace Pipeline\Pype;

use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\BasePath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\Utilities\ArrayHelper;
use function Pipeline\Accessors\Configuration;
use ScssPhp\ScssPhp\Compiler as SCSSCompiler;

class AssetCompiler
{
    public function compileProjectStylesheets()
    {
        if (Configuration("development.compileSCSS")) {
            $smart_public_folders = FileSystem::find(new DirectoryPath(BasePath::DIR_PUBLIC), "css");
            $page_scss_folder = (new DirectoryPath(BasePath::DIR_WEB, "scss/"))->toString();
            $prefab_scss_folder = (new DirectoryPath(BasePath::DIR_PRIVATE, "SCSS/"))->toString();

            $scss_folders = ArrayHelper::stackLines([$prefab_scss_folder, $page_scss_folder], $smart_public_folders);

            $output_folder = (new DirectoryPath(BasePath::DIR_WEB, "css/"))->toString();
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
