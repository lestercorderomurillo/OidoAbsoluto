<?php

namespace Pipeline\PypeEngine\Inproc;

use Pipeline\Traits\StringableTrait;

class HTMLStrip
{
    use StringableTrait;

    public string $tag;
    public array $attributes;

    public function __construct($tag, array $attributes = [])
    {
        $this->tag = strtolower($tag);
        $this->attributes = $attributes;
    }

    public function toString(): string
    {
        $attributes_string = "";
        foreach ($this->attributes as $key => $value) {
            if (is_int($key)) {
                $attributes_string .= " $value";
            } else if(strlen($value) > 0){
                $attributes_string .= " $key=\"$value\"";
            }
        }
        return "<" . trim($this->tag . $attributes_string) . ">";
    }
}
