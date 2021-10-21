<?php

namespace Pipeline\PypeEngine;

use Pipeline\Traits\DefaultAccessorTrait;
use Pipeline\Utilities\ArrayHelper;

class PypeTemplate
{
    use DefaultAccessorTrait;

    private array $required;
    private array $defaults;
    private array $stateful;

    private string $prototype;
    private string $template;
    
    private string $class;
    private string $name;

    private string $scripts;
    private string $awake;

    private bool $inline;

    public function __construct(string $name, array $definition)
    {
        $this->buildTemplate($name, $definition);
    }

    private function buildTemplate(string $name, array $definition)
    {
        $this->name = $name;

        $this->required = $this->tryGet($definition["required"], []);
        $this->stateful = $this->tryGet($definition["stateful"], []);

        $this->prototype = $this->tryGet($definition["prototype"], "div");
        $this->template = trim($definition["render"]);
        
        $this->class = trim($this->tryGet($definition["class"], $this->name));
        $this->inline = $this->hasKey($definition, "inline");
        $this->scripts = $this->tryGet($definition["scripts"], "");
        $this->awake = $this->tryGet($definition["awake"], "");

        $this->defaults = ArrayHelper::merge2DArray(true, [
            "id" => "",
            "name" => "",
            "class" => $this->class,
            "classes" => "",
            "accent" => "secondary"
        ], $this->tryGet($definition["defaults"], []));

        ArrayHelper::appendKeyPrefix("this", $this->defaults);
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
        return $this->template;
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
