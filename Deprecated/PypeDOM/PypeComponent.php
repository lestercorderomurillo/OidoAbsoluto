<?php

namespace Pipeline\PypeDOM;

use Pipeline\Core\DI;
use Pipeline\Utilities\Collection;
use Pipeline\Traits\StringableTrait;
use Pipeline\Core\Exceptions\CompileException;
use Pipeline\Utilities\Pattern;

class PypeComponent
{
    use StringableTrait;

    private PypeTemplate $template;
    private PypeTemplateBatch $template_batch;
    
    private string $body;

    private array $attributes;
    private array $external_context;
    
    public static function create(string $input_string, array $external_context = []): PypeComponent
    {
        $object = PypeCompiler::elementStringToArray($input_string);
        $template_batch = DI::getDependency(PypeTemplateBatch::class);

        return new PypeComponent($template_batch->getTemplate($object[0]), $object[1], $external_context);
    }

    public function __construct(PypeTemplate $template, array $attributes = [], array $external_context = [])
    {
        $this->compiler = DI::getDependency(PypeCompiler::class);
        $this->template_batch = DI::getDependency(PypeTemplateBatch::class);

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

        $this_context = Collection::mergeDictionary(true, $this->external_context, $template_defaults, 
        [
            "this" => $this->template->getComponentName(),
            "this:body" => $this->body,
        ]);

        foreach ($this->attributes as $key => $value) {
            $this_context["this:$key"] = $value;
        }

        ksort($this_context);

        $stacked = Pattern::substituteTokens($this->template->getAwakeScript(), $this_context); 
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
