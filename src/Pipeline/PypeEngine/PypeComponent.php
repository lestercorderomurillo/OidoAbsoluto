<?php

namespace Pipeline\PypeEngine;


use Pipeline\Security\Cryptography;
use Pipeline\Utilities\ArrayHelper;
use Pipeline\Traits\StringableTrait;
use Pipeline\PypeEngine\Exceptions\CompileException;

use function Pipeline\Accessors\Dependency;
use function Pipeline\Accessors\Session;

class PypeComponent
{
    use StringableTrait;

    private PypeTemplate $template;
    private array $context;

    private array $attributes;
    private string $inner_body;

    public static function create(string $input, array $context = []): PypeComponent
    {
        $parsed = PypeCompiler::componentStringToArray($input);
        $template = new PypeTemplate($parsed[0]);

        return new PypeComponent($template, $parsed[1], $context);
    }

    public function __construct(PypeTemplate $template, array $attributes = [], array $context = [])
    {
        $this->compiler = Dependency(PypeCompiler::class);
        $this->template = $template;
        $this->attributes = $attributes;
        $this->context = $context;
        $this->inner_body = "";

        foreach ($this->attributes as $attribute => $value) {
            if (is_int($attribute) && is_null($value)) {
                throw new CompileException("Invalid component keyvalue.");
            }
        }
        
        foreach ($template->getRequiredAttributes() as $attribute) {
            if (!isset($this->attributes[$attribute])) {
                $proto = $template->getComponentName();
                throw new CompileException("The component [$proto] is missing the required attribute: [$attribute]");
            }
        }
    }

    public function setBody(string $inner_body): PypeComponent
    {
        $this->inner_body = $inner_body;
        return $this;
    }

    public function render(): string
    {
        $defaults = $this->template->getDefaultAttributes();
        $viewtokens = [];
        $atokens = [];

        $systokens =
        [
            "this" => $this->template->getComponentName(),
            "this.body" => $this->inner_body,
            "this.class" => $this->template->getComponentClass()
        ];

        foreach(Session()->expose() as $key => $value){
            $systokens["session.$key"] = $value;
        }

        foreach($this->attributes as $key => $value){
            $atokens["this.$key"] = $value;
        }

        $this->context = ArrayHelper::mergeNamedValues($this->context, $viewtokens, $defaults, $atokens, $systokens);
        ksort($this->context);
        return PypeCompiler::renderString($this->template->getRenderTemplateString(), $this->context);
    }

    public function toString(): string {
        return $this->render();
    }
}
