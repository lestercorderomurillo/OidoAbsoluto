<?php

namespace Pipeline\PypeEngine\Boot;

use ScssPhp\ScssPhp\Compiler;
use Pipeline\Utilities\Vector;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\Path;

use function Pipeline\Kernel\configuration;

class SCSSCompiler
{
    public function compileProjectStylesheets()
    {
        if (configuration("development.compileSCSS")) {

            // Find all bootstrap, font-awesome, etc style files
            $packages_files = FileSystem::find(new DirectoryPath(SystemPath::PACKAGES), "css");

            // Get build components style files
            $prefabs_components_folder = (new DirectoryPath(SystemPath::PREFABS, "Components/"))->toString();

            // Get build style files
            $prefabs_scss_folder = (new DirectoryPath(SystemPath::SRC, "PypeEngine/Boot/Stylesheet/"))->toString();

            // Get page style files
            $page_folder = (new DirectoryPath(SystemPath::WEB))->toString();

            // Get user components style files
            $user_folder = new DirectoryPath(SystemPath::USERCOMPONENTS);

            $scss_folders = Vector::stackLines(
                $packages_files,
                [
                    $page_folder,
                    $prefabs_scss_folder,
                    $prefabs_components_folder,
                    $user_folder->toString(),
                ]
            );

            $scss_compiler = new Compiler();
            $scss_compiler->setImportPaths($scss_folders);

            $entry_scss = (new Path(SystemPath::SRC, "PypeEngine/Boot/Stylesheet/compiled", "scss"))->toString();
            $output_scss = (new Path(SystemPath::WEB, "build", "css"))->toString();

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
