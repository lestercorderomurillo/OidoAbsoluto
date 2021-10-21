<?php

namespace Pipeline\PypeEngine\Inproc;

use Pipeline\Utilities\ArrayHelper;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\FilePath;
use ScssPhp\ScssPhp\Compiler as ScssPhpCompiler;
use function Pipeline\Accessors\Configuration;

class SCSSCompiler
{
    public function compileProjectStylesheets()
    {
        if (Configuration("development.compileSCSS")) {

            // Find all bootstrap, font-awesome, etc style files
            $packages_files = FileSystem::find(new DirectoryPath(SystemPath::PACKAGES), "css");

            // Get build components style files
            $buildin_components_folder = (new DirectoryPath(SystemPath::BUILDIN, "Components/Styles/"))->toString();

            // Get build style files
            $buildin_scss_folder = (new DirectoryPath(SystemPath::BUILDIN, "SCSS/"))->toString();

            // Get page style files
            $page_folder = (new DirectoryPath(SystemPath::WEB))->toString();

            // Get user components style files
            $user_folder = new DirectoryPath(SystemPath::USERCOMPONENTS, "Styles/");

            $scss_folders = ArrayHelper::stackLines(
                $packages_files,
                [
                    $page_folder,
                    $buildin_scss_folder,
                    $buildin_components_folder,
                    $user_folder->toString(),
                ]
            );

            $scss_compiler = new ScssPhpCompiler();
            $scss_compiler->setImportPaths($scss_folders);

            $entry_scss = (new FilePath(SystemPath::BUILDIN, "SCSS/compiled", "scss"))->toString();
            $output_scss = (new FilePath(SystemPath::WEB, "build", "css"))->toString();

            $string_scss = file_get_contents($entry_scss);

            $user_files = FileSystem::find($user_folder, "scss");
            foreach($user_files as $user_file){
                $string_scss .= " @import \"".FileSystem::simplifyPath($user_file)."\";";
            }
            
            $string_css = $scss_compiler->compile($string_scss);
            file_put_contents($output_scss, $string_css);
        }
    }
}
