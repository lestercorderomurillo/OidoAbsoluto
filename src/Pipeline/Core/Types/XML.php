<?php

namespace Pipeline\Core\Types;

use Pipeline\Traits\StringableTrait;

class XML 
{
    use StringableTrait;
    
    private int $hints;
    private $value;

    public static function create($value, int $hints = 0){
        $instance = new XML($value, $hints);
        return $instance;
    }

    public function __construct($value, int $hints = 0)
    {
        $this->value = $value;
        $this->hints = $hints;
    }

    public function addHint(int $hint): XML
    {
        $this->hints += $hint;
        return $this;
    }

    public function toJavascriptString(): string
    {
        return "Unimplemented";
    }

    public function toString(): string
    {
        return "Unimplemented";
    }
}
