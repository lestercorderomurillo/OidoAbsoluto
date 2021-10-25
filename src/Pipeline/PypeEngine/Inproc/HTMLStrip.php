<?php

namespace Pipeline\PypeEngine\Inproc;

use Pipeline\Traits\StringableTrait;
use Pipeline\Utilities\StringHelper;

class HTMLStrip
{
    use StringableTrait;

    private string $tag;
    private array $attributes;

    public function __construct(string $tag, array $attributes = [])
    {
        $this->tag = strtolower($tag);
        $this->attributes = $attributes;
    }

    public function toString(): string
    {
        $attributes_string = "";

        $this->attributes = StringHelper::parseMultivalueFields($this->attributes);

        foreach ($this->attributes as $key => $value) {
            if (is_int($key)) {
                $attributes_string .= " $value";
            } else {
                if ($key == "html") {
                    $attributes_string .= " $key";
                } else if ($key == "id" || $key == "name" || $key == "for") {
                    if (strlen($value) > 0) {
                        $attributes_string .= " $key=\"$value\"";
                    }
                } else {
                    $attributes_string .= " $key=\"$value\"";
                }
            }
        }
        return "<" . trim($this->tag . $attributes_string) . ">";
    }
}
