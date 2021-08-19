<?php

namespace Pipeline\Core\Types;

use Pipeline\Traits\SerializableTrait;

class JSON 
{
    use SerializableTrait;
    
    private int $hints;
    private $value;

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

    public function toJavascriptString(): string
    {
        return addslashes(json_encode($this->value, $this->hints));
    }

    public function toString(): string
    {
        return json_encode($this->value, $this->hints);
    }
}
