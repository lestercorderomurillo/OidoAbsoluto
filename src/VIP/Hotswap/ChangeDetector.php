<?php

namespace VIP\Hotswap;

use VIP\Core\BaseObject;
use VIP\FileSystem\BasePath;
use VIP\FileSystem\DirectoryPath;
use VIP\FileSystem\FilePath;
use VIP\FileSystem\FileSystem;
use VIP\HTTP\Server\Response\JSON;

class ChangeDetector extends BaseObject
{
    public static function compareTimestamps(string $page, string $user_timestamp): bool
    {
        $timestamp_path = new FilePath(BasePath::DIR_SRC, "Hotswap/timestamp", "json");
        if (!FileSystem::exists($timestamp_path)) {
            self::saveTimestamps();
        }

        $hotswap_json = FileSystem::includeAsString($timestamp_path);
        $hotswap_values = json_decode($hotswap_json, true);

        $hotswap_timestamp = self::staticTryGet($hotswap_values[$page], "");

        return ($hotswap_timestamp != $user_timestamp);
    }

    public static function saveTimestamps()
    {
        FileSystem::writeToDisk(new FilePath(BasePath::DIR_SRC, "Hotswap/timestamp", "json"), self::generateTimestamps(), 0);
    }

    public static function generateTimestamps(): string
    {
        $paths = FileSystem::find(new DirectoryPath(BasePath::DIR_VIEWS), "phtml");
        $last_timestamp = [];
        foreach ($paths as $path) {
            $last_timestamp[md5($path)] = filemtime($path);
        }
        return (new JSON($last_timestamp, JSON_PRETTY_PRINT))->toString();
    }

    public static function generateTimestampForView(string $controller_name, string $view_name): string
    {
        $path = (new FilePath(BasePath::DIR_VIEWS, "$controller_name/$view_name", "phtml"))->toString();
        return filemtime($path);
    }
}
