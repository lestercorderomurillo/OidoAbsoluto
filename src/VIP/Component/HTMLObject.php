<?php

namespace VIP\Component;

use VIP\Core\BaseObject;
use VIP\Core\RenderableInterface;

class HTMLObject extends BaseObject implements RenderableInterface
{
    private string $tag;
    private bool $closure;
    private bool $append_closure;
    private array $attributes;
    private string $render_before;
    private string $render_after;

    public function __construct(string $tag, array $attributes = [])
    {
        $this->tag = $tag;
        $this->closure = false;
        $this->append_closure = false;
        $this->attributes = $attributes;
        $this->render_before = "";
        $this->render_after = "";

        if (($tag[0] == "/")) {
            $this->tag = substr($tag, 1);
            $this->closure = true;
        }
    }

    public function preRenderBefore(string $string): void
    {
        $this->render_before = $string;
    }

    public function preRenderAfter(string $string): void
    {
        $this->render_after = $string;
    }

    public function appendClosure(): void
    {
        $this->append_closure = true;
    }

    public function isClosure(): bool
    {
        return $this->closure;
    }

    public function setClosure(bool $closure): void
    {
        $this->closure = $closure;
    }

    public function getDOMTag(): string
    {
        return $this->tag;
    }

    public function setAttribute(string $key, string $value)
    {
        $this->attributes["$key"] = $value;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $attribute)
    {
        return $this->attributes[$attribute];
    }

    public function hasAttribute(string $attribute): bool
    {
        foreach ($this->attributes as $key => $value) {
            if (is_int($key) && $value == $attribute) {
                return true;
            }
        }
        return array_key_exists($attribute, $this->attributes);
    }

    public function render(): string
    {
        $attributes_string = "";
        foreach ($this->attributes as $key => $value) {
            if (is_int($key)) {
                $attributes_string .= " $value";
            } else {
                $attributes_string .= " $key=\"$value\"";
            }
        }
        $output = trim($this->tag . $attributes_string);
        if ($this->closure) {
            $output = "/" . $output;
        }
        if ($this->append_closure) {
            return "$this->render_before<$output></$this->tag>$this->render_after";
        } else {
            return "<$output>";
        }
    }
}
