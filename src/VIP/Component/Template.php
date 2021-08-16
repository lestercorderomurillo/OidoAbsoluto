<?php

namespace VIP\Component;

use VIP\Core\StaticLoaderInterface;
use VIP\Core\BaseObject;
use VIP\Core\ContainerInterface;
use VIP\FileSystem\BasePath;
use VIP\FileSystem\FileSystem;
use VIP\FileSystem\DirectoryPath;
use VIP\Utilities\ArrayHelper;

class Template extends BaseObject implements StaticLoaderInterface, ContainerInterface
{
    private array $content;
    private static $components;

    public static function onStaticLoad(): void
    {
        $native_components_file_names = FileSystem::find(new DirectoryPath(BasePath::DIR_INCLUDE, "private/components/", "php"));
        $user_components_file_names = FileSystem::find(new DirectoryPath(BasePath::DIR_APP, "components/", "php"));
        $components_to_include = ArrayHelper::merge($user_components_file_names, $native_components_file_names);

        self::$components = [];
        foreach ($components_to_include as $file) {
            self::$components = ArrayHelper::merge(self::$components, require_once($file));
        }
    }

    public static function isRegistered(string $component_name)
    {
        return (isset(self::$components[$component_name]));
    }

    public function __construct(string $component_name, int $depth = 0)
    {
        $this->content = self::$components[$component_name];
        $this->transverseToNextConcat($depth);
    }

    public function transverseToNextConcat(int $depth = 0)
    {
        for ($i = 0; $i < $depth; $i++) {
            if (isset($this->content["concat"])) {
                $this->content = $this->content["concat"];
            }
        }
    }

    public function expose(): array
    {
        return $this->content;
    }

    public function has(string $attribute): bool
    {
        foreach ($this->content as $key => $value) {
            if (is_int($key) && $value == $attribute) {
                return true;
            }
        }
        return (isset($this->content[$attribute]));
    }

    public function get(string $key, $default = NULL)
    {
        return $this->tryGet($this->content[$key], $default);
    }

    public function set(string $key, $value = NULL): void
    {
        $this->content[$key] = $value;
    }
}
