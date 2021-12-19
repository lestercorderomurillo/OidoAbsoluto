<?php

namespace Cosmic\DOM;

use Cosmic\Traits\StringableTrait;
use Cosmic\Core\Exceptions\CompileException;
use Cosmic\Utilities\Pattern;
use Cosmic\Utilities\Collection;
use Cosmic\Utilities\Text;

class Element
{
    use StringableTrait;

    private Component $component;
    private Compiler $compiler;
    private PypeDOM $pypeDOM;

    private string $body;
    private array $attributes;
    private array $inherit_context;

    public function __construct(PypeDOM $pypeDOM, Compiler $compiler)
    {
        $this->compiler = $compiler;
        $this->pypeDOM = $pypeDOM;
        $this->body = "";
    }

    public function setComponent(Component $component): Element
    {
        $this->component = $component;
        return $this;
    }

    public function setAttributes(array $attributes = []): Element
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function setInheritContext(array $inherit_context = []): Element
    {
        $this->inherit_context = $inherit_context;
        return $this;
    }

    public function setBody(string $body): Element
    {
        $this->body = $body;
        return $this;
    }

    private function validateComponent(): void
    {
        foreach ($this->attributes as $attribute => $value) {
            if (is_int($attribute) && is_null($value)) {
                throw new CompileException("Invalid component keyvalue in their attributes.");
            }
        }

        foreach ($this->component->getRequiredAttributes() as $attribute) {
            if (!isset($this->attributes[$attribute])) {
                $prototype = $this->component->getComponentName();
                throw new CompileException("The component [$prototype] is missing the required attribute: [$attribute]");
            }
        }
    }

    public function render(): string
    {
        $this->validateComponent();

        $component_defaults = $this->component->getDefaultAttributes();

        $context = Collection::mergeDictionary(
            $this->inherit_context,
            $component_defaults,
            [
                "this" => $this->component->getComponentName(),
                "this:body" => $this->body,
            ]
        );

        $this->attributes = Collection::mapKeys($this->attributes, function ($key) {
            if(!Text::startsWith($key, "this:") && $key != "this"){
                return "this:" . $key;
            }
            return $key;
        });

        $context = Collection::mergeDictionary($context, $this->attributes);

        ksort($context);

        $script = Pattern::substituteTokens($this->component->getAwakeScript(), $context);

        if (trim($script) != "") {
            $this->compiler->context_factory->addAwakeScripts($script);
        }

        return $this->compiler->renderString($this->component->getRenderTemplate(), $context);
    }

    public function toString(): string
    {
        return $this->render();
    }
}
