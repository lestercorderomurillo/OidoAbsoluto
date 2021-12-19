<?php

namespace Cosmic\Reload;

use Cosmic\Core\Types\JSON;
use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\Directory;
use Cosmic\FileSystem\Paths\File;
use Cosmic\Utilities\Collection;

use function Cosmic\Core\Boot\safe;

class ChangeDetector
{
    public static function compareTimestamps(string $page, string $user_timestamp): bool
    {
        $timestamp_path = new File("src/Cosmic/Reload/timestamp.json");

        if (!FileSystem::exists($timestamp_path)) {
            self::saveTimestamps();
        }

        $values = Collection::from($timestamp_path);

        $reload_timestamp = safe($values[$page], "");

        return ($reload_timestamp != $user_timestamp);
    }

    public static function saveTimestamps()
    {
        FileSystem::write(new File("src/Cosmic/Reload/timestamp.json"), self::generateTimestamps());
    }

    public static function generateTimestamps(): string
    {
        $paths = FileSystem::find(new Directory("app/Views/"), "cosmic");

        $last_timestamp = [];

        foreach ($paths as $path) {
            $last_timestamp[md5($path)] = filemtime($path);
        }

        return (new JSON($last_timestamp, JSON_PRETTY_PRINT))->toString();
    }

    public static function generateTimestampForView(string $controller_name, string $view_name): string
    {
        $path = new File("app/Views/$controller_name/$view_name.cosmic");
        return filemtime($path);
    }
}
