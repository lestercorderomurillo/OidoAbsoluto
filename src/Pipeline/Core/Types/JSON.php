<?php

namespace Pipeline\Core\Types;

use Pipeline\Traits\StringableTrait;

class JSON 
{
    use StringableTrait;
    
    private int $hints;
    private $value;

    public static function create($value, int $hints = 0){
        $instance = new JSON($value, $hints);
        return $instance;
    }

    public function __construct($value, int $hints = 0)
    {
        $this->value = $value;
        $this->hints = $hints;
    }

    public function addHint(int $hint): JSON
    {
        $this->hints += $hint;
        return $this;
    }

    public function toString(): string
    {
        return json_encode($this->value, $this->hints);
    }
}
