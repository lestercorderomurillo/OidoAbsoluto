<?php

namespace Pipeline\Pype\Template;

use Pipeline\Utilities\ArrayHelper;
use Pipeline\Core\StaticObjectInterface;
use Pipeline\Core\Container\ContainerInterface;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\BasePath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\Traits\DefaultAccessorTrait;

class ComponentDefinition implements ContainerInterface, StaticObjectInterface
{
    use DefaultAccessorTrait;

    private array $content;
    private static $components;

    public function __construct(string $component_name, int $depth = 0)
    {
        $this->content = self::$components[$component_name];
        $this->transverseToNextConcat($depth);
    }

    public static function __initialize(): void
    {
        $native_components_file_names = FileSystem::find(new DirectoryPath(BasePath::DIR_INCLUDE, "private/components/", "php"));
        $user_components_file_names = FileSystem::find(new DirectoryPath(BasePath::DIR_APP, "components/", "php"));
        $components_to_include = ArrayHelper::stackLines($native_components_file_names, $user_components_file_names);

        self::$components = [];
        foreach ($components_to_include as $file) {
            $component = require_once($file);
            self::$components = self::customKeyMerge($component, self::$components);
        }
    }

    public static function customKeyMerge(array ...$arrays_to_mix)
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

    public static function isRegistered(string $component_name)
    {
        return (isset(self::$components[$component_name]));
    }

    public function transverseToNextConcat(int $depth = 0)
    {
        for ($i = 0; $i < $depth; $i++) {
            if (isset($this->content["concatElement"])) {
                $this->content = $this->content["concatElement"];
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
