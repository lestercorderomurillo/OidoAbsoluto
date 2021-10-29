<?php

namespace Pipeline\PypeEngine;

use Pipeline\Core\Loader;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\Utilities\Vector;
use Pipeline\Traits\DefaultAccessorTrait;

class PypeTemplateBatch extends Loader
{
    use DefaultAccessorTrait;
    private static $templates;

    protected static function __load(): void
    {
        $template_file_names = FileSystem::find(new DirectoryPath(SystemPath::COMPONENTS));
        $user_template_file_names = FileSystem::find(new DirectoryPath(SystemPath::USERCOMPONENTS));

        $templates_to_include = Vector::stackLines($template_file_names, $user_template_file_names);

        $templates_imported = [];
        foreach ($templates_to_include as $file) {
            $template = require_once($file);
            if (!is_int($template)) {
                $templates_imported = self::joinKeyMerge($template, $templates_imported);
            }
        }

        self::$templates = [];
        foreach ($templates_imported as $name => $definition) {
            self::$templates[$name] = new PypeTemplate($name, $definition);
        }
    }

    public static function getTemplate(string $component_name): PypeTemplate
    {
        self::load();
        return (self::$templates[$component_name]);
    }

    public static function getTemplates(): array
    {
        self::load();
        return self::$templates;
    }

    public static function isRegistered(string $component_name): bool
    {
        self::load();
        return (isset(self::$templates[$component_name]));
    }

    private static function joinKeyMerge(array ...$arrays_to_mix): array
    {
        $result_array = [];
        foreach ($arrays_to_mix as $array) {
            foreach ($array as $key => $value) {
                $result_array["$key"] = $value;
            }
        }
        return $result_array;
    }
}
