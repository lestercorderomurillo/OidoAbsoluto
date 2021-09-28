<?php

namespace Pipeline\FileSystem;

use Pipeline\FileSystem\Path\AbstractPath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\FilePath;
use Pipeline\FileSystem\Path\Web\WebPath;
use Pipeline\HTTP\Server\ServerResponse;

class FileSystem
{

    public static function simplifyPath(string $path): string
    {
        $last_index = strripos($path, "/");
        return substr($path, $last_index + 1);
    }

    private static function recursiveGlob($pattern, $flags = 0): array
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, self::recursiveGlob($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }

    public static function exists(AbstractPath $path): bool
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
        $exposed_paths = [];

        if (!is_array($array_of_paths)) {
            $value = $array_of_paths;
            $array_of_paths = [];
            $array_of_paths[0] = $value;
        }

        foreach ($array_of_paths as $path) {
            if ($path instanceof AbstractPath) {
                $path = $path->toString();
            }
            $exposed_paths[] = WebPath::create($path)->toString();
        }
        return $exposed_paths;
    }

    public static function findWebPaths(DirectoryPath $folder_path, string $extension): array
    {
        $exposed_paths = [];
        if (($extension = strtolower($extension)) == "php") {
            ServerResponse::create(403)->sendAndExit();
        }

        $internal_paths = self::find($folder_path, $extension);

        foreach ($internal_paths as $path) {
            $exposed_paths[] = WebPath::create($path)->toString();
        }

        return $exposed_paths;
    }

    public static function includeAsString(FilePath $path, bool $crash_on_failure = true): string
    {
        if (!file_exists($path)) {
            if ($crash_on_failure) {
                ServerResponse::create(500, "Invalid resource name [$path] to include as string. 
                Check your API files.")->sendAndExit();
            } else {
                die("Cannot find: " . $path->toString() . " from: " . debug_backtrace()[1]['function'] . "()");
                return NULL;
            }
        }

        ob_start();
        include($path->toString());
        $output_html = ob_get_contents();
        ob_end_clean();
        return $output_html;
    }

    public static function requireFromFile(FilePath $path)
    {
        if (!file_exists($path->toString())) {
            ServerResponse::create(500, "Invalid resource name to require. Check your controller files.")->sendAndExit();
        }
        return require_once($path->toString());
    }

    public static function writeToDisk(FilePath $path, string $value, int $mode = FILE_APPEND): void
    {
        file_put_contents($path->toString(), $value . "\n", $mode);
    }
}
