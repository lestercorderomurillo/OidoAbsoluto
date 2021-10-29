<?php

namespace Pipeline\PypeEngine;

use Pipeline\Utilities\Vector;
use Pipeline\Traits\StringableTrait;
use Pipeline\Core\Exceptions\CompileException;

use function Pipeline\Kernel\dependency;

class PypeComponent
{
    use StringableTrait;

    private PypeTemplate $template;
    
    private string $body;

    private array $attributes;
    private array $external_context;
    
    public static function create(string $input_string, array $external_context = []): PypeComponent
    {
        $object = PypeCompiler::componentStringToArray($input_string);
        return new PypeComponent(PypeTemplateBatch::getTemplate($object[0]), $object[1], $external_context);
    }

    public function __construct(PypeTemplate $template, array $attributes = [], array $external_context = [])
    {
        $this->compiler = dependency(PypeCompiler::class);
        
        $this->body = "";
        $this->template = $template;
        $this->attributes = $attributes;
        $this->external_context = $external_context;
        $this->validateComponent();
    }

    private function validateComponent(): void
    {
        foreach ($this->attributes as $attribute => $value) {
            if (is_int($attribute) && is_null($value)) {
                throw new CompileException("Invalid component keyvalue in their attributes.");
            }
        }

        foreach ($this->template->getRequiredAttributes() as $attribute) {
            if (!isset($this->attributes[$attribute])) {
                $prototype = $this->template->getComponentName();
                throw new CompileException("The component [$prototype] is missing the required attribute: [$attribute]");
            }
        }
    }

    public function setBody(string $body): PypeComponent
    {
        $this->body = $body;
        return $this;
    }

    public function render(): string
    {
        $template_defaults = $this->template->getDefaultAttributes();

        $this_context = Vector::mergeNamedValues($this->external_context, $template_defaults, 
        [
            "this" => $this->template->getComponentName(),
            "this:body" => $this->body,
        ]);

        foreach ($this->attributes as $key => $value) {
            $this_context["this:$key"] = $value;
        }

        ksort($this_context);

        $stacked = Vector::parameterReplace($this->template->getAwakeScript(), $this_context); 
        if($stacked != "" && $stacked != " "){
            PypeCompiler::$context_factory->addAwakeScripts($stacked);
        }

        return PypeCompiler::renderString($this->template->getRenderTemplate(), $this_context);
    }

    public function toString(): string
    {
        return $this->render();
    }
}
