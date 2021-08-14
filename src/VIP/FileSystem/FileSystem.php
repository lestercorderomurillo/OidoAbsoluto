<?php

namespace VIP\FileSystem;

use VIP\Factory\ResponseFactory;

class FileSystem
{
    private static function recursiveGlob($pattern, $flags = 0): array
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, self::recursiveGlob($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }

    private static function findInPath(string $base_path, string $format): array
    {
        $paths = [];

        if (substr($base_path, -1) != "/") {
            $base_path .= "/";
        }

        $search_path = $base_path . "*.$format";
        foreach (self::recursiveGlob($search_path) as $file) {
            $paths[] = $file;
        }

        return $paths;
    }

    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    public static function find(string $base_path, string $format = "php"): array
    {
        return self::findInPath(__RDIR__ . $base_path, $format);
    }

    public static function findWebExposed(string $base_path, string $format): array
    {
        $format = strtolower($format);
        if ($format == "php") {
            return ResponseFactory::createError(403);
        }

        $composed_path = __LWEB__ . $base_path;
        $internal_paths = self::findInPath($composed_path, $format);
        $exposed_paths = [];
        foreach ($internal_paths as $path) {
            $exposed_paths[] = str_replace(__LWEB__, __URL__ . __FWEB__ . "/", $path);
        }

        return $exposed_paths;
    }

    public static function includeAsString(string $path, bool $crash_on_failure = true): string
    {
        if (!file_exists($path)) {
            if ($crash_on_failure) {
                ResponseFactory::createError("Invalid resource name. Check your controller files.", 500)->handle();
            } else {
                return NULL;
            }
        }
        ob_start();
        include($path);
        $var = ob_get_contents();
        ob_end_clean();
        return preg_replace("/\s+/", " ", $var);
    }

    public static function include(string $file_name)
    {
        return require_once(__RDIR__ . "/src/VIP/Include/$file_name.php");
    }
}
