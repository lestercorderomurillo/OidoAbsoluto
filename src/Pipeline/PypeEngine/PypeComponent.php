<?php

namespace Pipeline\PypeEngine;

use Pipeline\Pype\Exceptions\CompileException;
use Pipeline\PypeEngine\Inproc\RenderContext;
use Pipeline\Security\Cryptography;
use Pipeline\Utilities\ArrayHelper;
use Pipeline\Traits\StringableTrait;

use function Pipeline\Accessors\Dependency;
use function Pipeline\Accessors\Session;

class PypeComponent
{
    use StringableTrait;
    private PypeTemplate $template;
    private RenderContext $context;
    private array $attributes;
    private string $inner_body;

    public static function create(string $input, $context = null): PypeComponent
    {
        if($context == null){
            $context = new RenderContext();
        }else if(is_array($context)){
            $context = new RenderContext($context);
        }

        $parsed = PypeCompiler::componentStringToArray($input);
        $template = new PypeTemplate($parsed[0]);

        return new PypeComponent($template, $parsed[1], $context);
    }

    public function __construct(PypeTemplate $template, array $attributes = [], RenderContext $context = null)
    {
        if($context == null){
            $context = new RenderContext();
        }

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
        $systokens =
            [
                "url" => __URL__,
                "random" => Cryptography::computeRandomKey(8),
                "this" => $this->template->getComponentName(),
                "this.body" => $this->inner_body,
            ];

        foreach(Session()->expose() as $key => $value){
            $systokens["session.$key"] = $value;
        }

        $defaults = $this->template->getDefaultAttributes();

        foreach ($this->context->expose() as $key => $value) {
            if(!isset($this->attributes[$key])){
                $this->attributes[$key] = $value;
            }
        }

        $domtokens = $this->attributes;
        $tokens = ArrayHelper::mergeNamedValues($systokens, $defaults, $domtokens);
        $this->context->set("componentClass", $this->template->getComponentClass()); 

        return PypeCompiler::renderString($this->template->getRenderTemplateString(), $tokens, $this->context);
    }

    public function toString(): string {
        return $this->render();
    }
}
