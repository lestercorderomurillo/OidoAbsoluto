<?php

namespace Pipeline\Reload;

use Pipeline\Core\Types\JSON;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\ServerPath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\Path;
use function Pipeline\Kernel\safe;

class ChangeDetector
{
    public static function compareTimestamps(string $page, string $user_timestamp): bool
    {
        $timestamp_path = new Path(ServerPath::SRC, "Reload/timestamp", "json");
        if (!FileSystem::exists($timestamp_path)) {
            self::saveTimestamps();
        }

        $Reload_json = FileSystem::includeAsString($timestamp_path);
        $Reload_values = json_decode($Reload_json, true);

        $Reload_timestamp = safe($Reload_values[$page], "");

        return ($Reload_timestamp != $user_timestamp);
    }

    public static function saveTimestamps()
    {
        FileSystem::writeToDisk(new Path(ServerPath::SRC, "Reload/timestamp", "json"), self::generateTimestamps(), 0);
    }

    public static function generateTimestamps(): string
    {
        $paths = FileSystem::find(new DirectoryPath(ServerPath::VIEWS), "phtml");
        $last_timestamp = [];
        foreach ($paths as $path) {
            $last_timestamp[md5($path)] = filemtime($path);
        }
        return (new JSON($last_timestamp, JSON_PRETTY_PRINT))->toString();
    }

    public static function generateTimestampForView(string $controller_name, string $view_name): string
    {
        $path = (new Path(ServerPath::VIEWS, "$controller_name/$view_name", "phtml"))->toString();
        return filemtime($path);
    }
}
