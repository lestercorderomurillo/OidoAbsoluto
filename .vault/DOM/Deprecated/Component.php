<?php

namespace Cosmic\DOM;

use Cosmic\Utilities\Collection;
use function Cosmic\Kernel\safe;

class Component
{
    private array $required;
    private array $defaults;
    private array $stateful;

    private string $prototype;
    private string $render_template;
    
    private string $class;
    private string $name;

    private string $scripts;
    private string $awake;

    private bool $inline;

    public function __construct(string $name, array $definition)
    {
        $this->buildComponent($name, $definition);
    }

    private function buildComponent(string $name, array $definition)
    {
        $this->name = $name;

        $this->required = safe($definition["required"], []);
        $this->stateful = safe($definition["stateful"], []);

        $this->prototype = safe($definition["prototype"], "div");
        $this->render_template = trim($definition["render"]);
        
        $this->class = trim(safe($definition["class"], $this->name));
        $this->inline = $this->hasKey($definition, "inline");
        $this->scripts = safe($definition["scripts"], "");
        $this->awake = safe($definition["awake"], "");

        $this->defaults = Collection::mergeDictionary([
            "id" => "",
            "name" => "",
            "class" => $this->class,
            "classes" => "",
            "accent" => "secondary"
        ], safe($definition["defaults"], []));

        $this->defaults = Collection::mapKeys($this->defaults, function($key){
            return "this:" . $key;
        });
    }

    public function hasStatefulKey(string $attribute): bool
    {
        return ($this->hasKey($this->stateful, $attribute));
    }

    private function hasKey(array $source, string $attribute): bool
    {
        foreach ($source as $key => $value) {
            if (is_int($key) && $value == $attribute) {
                return true;
            }
        }
        return false;
    }

    public function getRenderTemplate(): string
    {
        return $this->render_template;
    }

    public function getRequiredAttributes(): array
    {
        return $this->required;
    }

    public function getDefaultAttributes(): array
    {
        return $this->defaults;
    }

    public function getComponentName(): string
    {
        return $this->name;
    }

    public function getComponentClass(): string
    {
        return $this->class;
    }

    public function getPrototype(): string
    {
        return $this->prototype;
    }

    public function getStatefulFields(): array
    {
        return $this->stateful;
    }

    public function getScripts(): string
    {
        return $this->scripts;
    }

    public function getAwakeScript(): string
    {
        return $this->awake;
    }

    public function isInlineComponent(): bool
    {
        return $this->inline;
    }
}
