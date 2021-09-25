<?php

namespace Pipeline\Pype\Component;

use Pipeline\Core\IdentifiableObject;
use Pipeline\HTTP\Server\ServerResponse;
use Pipeline\Pype\Exceptions\CompileException;
use Pipeline\Utilities\ArrayHelper;
use Pipeline\Utilities\StringHelper;

class HTMLObject extends IdentifiableObject implements DOMInterface
{
    private string $tag;
    private array $attributes;

    private bool $closure;
    private bool $append_closure;

    private string $render_before;
    private string $render_inside;
    private string $render_after;

    public static function create(string $string_input, array $array_input = []): HTMLObject
    {
        $tag_att_split = explode(" ", $string_input, 2);
        $tag = $tag_att_split[0];
        $attributes = [];

        if (isset($tag_att_split[1])) {
            $attributes_split = StringHelper::quotedExplode($tag_att_split[1]);

            foreach ($attributes_split as $attribute) {
                $key_value_split = StringHelper::multiExplode(["='", "=\""], $attribute);
                $key = $key_value_split[0];

                if (isset($key_value_split[1])) {
                    $value = $key_value_split[1];
                } else {
                    $value = NULL;
                }

                if (isset($value)) {
                    if ($value[strlen($value) - 1] == "\"" || $value[strlen($value) - 1] == "'") {
                        $value = substr($value, 0, -1);
                    } else {
                        ServerResponse::create(500, "Internal Server Error: Invalid key value. Check your view files.")->send();
                    }
                }

                $attributes["$key"] = $value;
            }
        }

        $attributes = ArrayHelper::mergeNamedValues($attributes, $array_input);
        ksort($attributes);
        return new HTMLObject($tag, $attributes);
    }

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

    public function preRenderBefore(string $string): HTMLObject
    {
        $this->render_before = $string;
        return $this;
    }

    public function preRenderBody(string $string): HTMLObject
    {
        $this->render_inside = $string;
        return $this;
    }

    public function preRenderAfter(string $string): HTMLObject
    {
        $this->render_after = $string;
        return $this;
    }

    public function appendClosure(): HTMLObject
    {
        $this->append_closure = true;
        return $this;
    }

    public function isClosure(): bool
    {
        return $this->closure;
    }

    public function setClosure(bool $closure): HTMLObject
    {
        $this->closure = $closure;
        return $this;
    }

    public function getDOMTag(): string
    {
        return $this->tag;
    }

    public function setAttribute(string $key, string $value): HTMLObject
    {
        $this->attributes["$key"] = $value;
        return $this;
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
        if ($this->closure && $this->append_closure) {
            throw new CompileException("HTML Object cannot be appended a new closure if it is already one.");
        }

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

        if (!isset($this->render_inside)) {
            $this->render_inside = "";
        }

        if ($this->append_closure) {
            return "$this->render_before<$this->tag>$this->render_inside</$this->tag>$this->render_after";
        } else {
            return "$this->render_before<$output>$this->render_after";
        }
    }
}
