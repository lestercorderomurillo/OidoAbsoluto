<?php

namespace Cosmic\DOM;

use Cosmic\Core\Types\View;
use Cosmic\Utilities\Collection;
use function Cosmic\Kernel\safe;

class CosmicCompiler
{


    public function compileString(string $string, array $public_tokens = [],  array $private_tokens = [], int $depth = 0)
    {
    }

    public function compileElement(): string
    {
    }

    public function compileFor(): string
    {
    }

    public function compileForeach(): string
    {
    }

    public function compileIf(): string
    {
    }

    public function compileExpression(): string
    {
    }

    public function compileMathExpression(): string
    {
    }

    public function clearBuffer(): string
    {
    }

    public function getBuffer(): string
    {
    }

    public function writeOnBuffer(Selection &$selection, string &$stream, string $replace_string): string
    {
        $pre_selection_string = substr($stream, 0, $selection->getStartPosition());
        $post_selection_string = substr($stream, $selection->getEndPosition() + 1);
        return $pre_selection_string . $replace_string . $post_selection_string;
    }


    public function removeKeyword(string $tag, string $keyword, bool $closure): ?string
    {
        return substr($tag, strlen($keyword) + $closure);
    }

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
            $prefabs_scss_folder = (new DirectoryPath(ServerPath::SRC, "DOM/Runtime/Stylesheet/"))->toString();

            // Get page style files
            $page_folder = (new DirectoryPath(ServerPath::WEB))->toString();

            // Get user components style files
            $user_folder = new DirectoryPath(ServerPath::USERCOMPONENTS);

            $scss_folders = Collection::mergeList(
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

            $entry_scss = (new Path(ServerPath::SRC, "DOM/Runtime/Stylesheet/compiled", "scss"))->toString();
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
