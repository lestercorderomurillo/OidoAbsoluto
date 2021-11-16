<?php

namespace Pipeline\HTTP;

class ErrorMessage extends Message {

    public function __construct(string $value = "")
    {
        $this->value = "";
    }
}
