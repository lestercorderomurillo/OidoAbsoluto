<?php

namespace Pipeline\PypeEngine;

use Exception;
use Pipeline\Core\StaticObjectInitializer;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\Utilities\ArrayHelper;

class PypeTemplateBatch extends StaticObjectInitializer
{
    private static $components;

    protected static function __initialize(): void
    {
        $native_components_file_names = FileSystem::find(new DirectoryPath(SystemPath::COMPONENTS));
        $user_components_file_names = FileSystem::find(new DirectoryPath(SystemPath::USERCOMPONENTS));

        $components_to_include = ArrayHelper::stackLines($native_components_file_names, $user_components_file_names);

        self::$components = [];
        foreach ($components_to_include as $file) {
            $component = require_once($file);
            if (!is_int($component)) {
                self::$components = self::customKeyMerge($component, self::$components);
            }
        }
    }

    public static function getComponentNativeArray(string $component_name): array
    {
        self::invokeInitialize();
        try {
            return self::$components[$component_name];
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public static function isRegistered(string $component_name)
    {
        self::invokeInitialize();
        return (isset(self::$components[$component_name]));
    }

    private static function customKeyMerge(array ...$arrays_to_mix)
    {
        $result_array = [];
        foreach ($arrays_to_mix as $array) {
            if (is_array($array)) {
                foreach ($array as $key => $value) {
                    $result_array["$key"] = $value;
                }
            } else {
                throw new \InvalidArgumentException("Only can merge arrays, not strings.");
            }
        }

        return $result_array;
    }
}
