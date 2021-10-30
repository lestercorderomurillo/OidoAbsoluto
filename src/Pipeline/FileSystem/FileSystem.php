<?php

namespace Pipeline\FileSystem;

use Pipeline\FileSystem\Path\PathBase;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\Path;
use Pipeline\FileSystem\Path\Web\WebPath;
use Pipeline\HTTP\Server\ServerResponse;
use function Pipeline\Kernel\app;
use function Pipeline\Kernel\fatal;

class FileSystem
{

    public static function simplifyPath(string $path): string
    {
        $last_index = strripos($path, "/");
        return substr($path, $last_index + 1);
    }

    public static function exists(PathBase $path): bool
    {
        return file_exists($path->toString());
    }

    public static function find(DirectoryPath $folder_path, string $extension = "php"): array
    {
        $path = $folder_path->toString();
        $paths = [];

        if (substr($path, -1) != "/") {
            $path .= "/";
        }

        $search_path = $path . "*.$extension";
        foreach (self::recursiveGlob($search_path) as $file) {
            $paths[] = $file;
        }

        return $paths;
    }

    public static function toWebPaths($array_of_paths): array
    {
        $exposeArrayd_paths = [];

        if (!is_array($array_of_paths)) {
            $value = $array_of_paths;
            $array_of_paths = [];
            $array_of_paths[0] = $value;
        }

        foreach ($array_of_paths as $path) {
            if ($path instanceof PathBase) {
                $path = $path->toString();
            }
            $exposeArrayd_paths[] = WebPath::create($path)->toString();
        }
        return $exposeArrayd_paths;
    }

    public static function findWebPaths(DirectoryPath $folder_path, string $extension): array
    {
        $exposeArrayd_paths = [];
        if (($extension = strtolower($extension)) == "php") {
            ServerResponse::create(403)->sendAndExit();
        }

        $internal_paths = self::find($folder_path, $extension);

        foreach ($internal_paths as $path) {
            $exposeArrayd_paths[] = WebPath::create($path)->toString();
        }

        return $exposeArrayd_paths;
    }

    public static function includeAsString(Path $path, bool $crash_with_view = true): string
    {
        if (!file_exists($path)) {
            $text = "Invalid resource name [$path] to include as string. Check your API files.";
            if (!app()->getRuntimeEnvironment()->inProductionMode()) {
                $text .= "<br>Cannot find: " . $path->toString() . " from: " . debug_backtrace()[1]['function'] . "()";
            }
            if ($crash_with_view) {
                fatal($text);
            } else {
                die($text);
            }
        }

        ob_start();
        include($path->toString());
        $output_html = ob_get_contents();
        ob_end_clean();
        return $output_html;
    }

    public static function requireFromFile(Path $path)
    {
        if (!file_exists($path->toString())) {
            fatal("Invalid resource name to require. Check your controller files.");
        }
        return require_once($path->toString());
    }

    public static function writeToDisk(Path $path, string $value, int $mode = FILE_APPEND): void
    {
        if (!file_exists(dirname($path->toString()))) {
            mkdir(dirname($path->toString()), 0777, true);
        }
        file_put_contents($path->toString(), $value . "\n", $mode);
    }

    private static function recursiveGlob($pattern, $flags = 0): array
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, self::recursiveGlob($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }
}
