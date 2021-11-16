<?php

namespace Pipeline\PypeDOM;

use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\ServerPath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\Utilities\Collection;

class PypeTemplateBatch
{
    private $templates;

    public function __construct()
    {
        $template_file_names = FileSystem::find(new DirectoryPath(ServerPath::COMPONENTS));
        $user_template_file_names = FileSystem::find(new DirectoryPath(ServerPath::USERCOMPONENTS));

        $templates_to_include = Collection::mergeList($template_file_names, $user_template_file_names);

        $templates_imported = [];
        foreach ($templates_to_include as $file) {
            $template = require_once($file);
            if (!is_int($template)) {
                $templates_imported = Collection::mergeDictionary(false, $template, $templates_imported);
            }
        }

        $this->templates = [];
        foreach ($templates_imported as $name => $definition) {
            $this->templates[$name] = new PypeTemplate($name, $definition);
        }
    }

    public function getTemplate(string $component_name): PypeTemplate
    {
        return ($this->templates[$component_name]);
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }

    public function isRegistered(string $component_name): bool
    {
        return (isset($this->templates[$component_name]));
    }

    /*private function joinKeyMerge(array ...$arrays_to_mix): array
    {
        $result_array = [];
        foreach ($arrays_to_mix as $array) {
            foreach ($array as $key => $value) {
                $result_array["$key"] = $value;
            }
        }
        return $result_array;
    }*/
}
