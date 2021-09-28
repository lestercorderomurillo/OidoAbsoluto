<?php

namespace Pipeline\PypeEngine;

use Pipeline\Traits\DefaultAccessorTrait;
use Pipeline\PypeEngine\Exceptions\CompileException;
use Pipeline\Utilities\ArrayHelper;

class PypeTemplate
{
    use DefaultAccessorTrait;

    private array $required;
    private array $defaults;
    private array $preserve;
    private string $prototype;
    private string $base_class;
    private string $template;
    private string $component_name;
    private bool $require_closure;
    private int $new_lines;

    public function __construct(string $component_name)
    {
        if ($component_name[0] == ".") {
            $component_name = substr($component_name, 1);
        }

        if (!PypeTemplateBatch::isRegistered($component_name)) {
            throw new CompileException("Trying to render the component \"$component_name\" failed. <br>
            Unregistered component. Check your view/components files.", 500);
        }

        $this->component_name = $component_name;
        $this->buildTemplate(PypeTemplateBatch::getComponentNativeArray($component_name));
    }

    public function buildTemplate(array $source)
    {
        $this->prototype = $this->tryGet($source["prototype"], "div");
        $this->template = trim($source["renderTemplate"]);

        $this->required = $this->tryGet($source["required"], []);
        $this->defaults = $this->tryGet($source["defaults"], []);

        $this->defaults = ArrayHelper::mergeNamedValues([
            "id" => "",
            "name" => "",
            "classes" => "",
            "accent" => "secondary"
        ], $this->defaults);

        foreach ($this->defaults as $key => $value){
            $this->defaults["this." . $key] = $value;
            unset($this->defaults[$key]);
        }

        //$this->preserve = $this->tryGet($source["preserve"], []);

        $this->component_class = trim($this->tryGet($source["componentClass"], "{this}"));

        $this->new_lines = $this->tryGet($source["newLine"], 0);
        $this->require_closure = !($this->hasKey($source, "inlineComponent"));
    }

    public function hasKey(array $source, string $attribute): bool
    {
        foreach ($source as $key => $value) {
            if (is_int($key) && $value == $attribute) {
                return true;
            }
        }
        return (isset($this->content[$attribute]));
    }

    public function getRenderTemplateString(): string
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
        return $this->component_name;
    }

    public function getPrototype(): string
    {
        return $this->prototype;
    }

    public function getPreservedFields(): array
    {
        return $this->preserve;
    }

    public function getComponentClass(): string
    {
        return $this->component_class;
    }

    public function getNumberOfNewLines(): int
    {
        return $this->new_lines;
    }

    public function requireClosure(): bool
    {
        return $this->require_closure;
    }
}
