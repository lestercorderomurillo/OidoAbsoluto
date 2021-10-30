<?php

namespace Pipeline\PypeEngine\Boot;

use ScssPhp\ScssPhp\Compiler;
use Pipeline\Utilities\Vector;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\ServerPath;
use Pipeline\FileSystem\Path\Local\Path;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use function Pipeline\Kernel\configuration;

class SCSSCompiler
{
    public function checkForMissingBuildStylesheet(): bool
    {
        return (FileSystem::exists(new Path(ServerPath::WEB, "build", "css")));
    }

    public function compileProjectStylesheets(): void
    {
        if (configuration("development.compileSCSS")) {

            // Find all bootstrap, font-awesome, etc style files
            $packages_files = FileSystem::find(new DirectoryPath(ServerPath::PACKAGES), "css");

            // Get build components style files
            $prefabs_components_folder = (new DirectoryPath(ServerPath::PREFABS, "Components/"))->toString();

            // Get build style files
            $prefabs_scss_folder = (new DirectoryPath(ServerPath::SRC, "PypeEngine/Boot/Stylesheet/"))->toString();

            // Get page style files
            $page_folder = (new DirectoryPath(ServerPath::WEB))->toString();

            // Get user components style files
            $user_folder = new DirectoryPath(ServerPath::USERCOMPONENTS);

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

            $entry_scss = (new Path(ServerPath::SRC, "PypeEngine/Boot/Stylesheet/compiled", "scss"))->toString();
            $output_scss = (new Path(ServerPath::WEB, "build", "css"))->toString();

            $string_scss = file_get_contents($entry_scss);

            $user_files = FileSystem::find($user_folder, "scss");
            foreach ($user_files as $user_file) {
                $string_scss .= " @import \"" . FileSystem::simplifyPath($user_file) . "\";";
            }

            $string_css = $scss_compiler->compile($string_scss);
            file_put_contents($output_scss, $string_css);
        }
    }
}
