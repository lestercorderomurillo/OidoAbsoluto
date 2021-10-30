<?php

namespace Pipeline\FileSystem\Path;

use Pipeline\Traits\StringableTrait;

abstract class PathBase
{
    use StringableTrait;
    
    protected string $base;
    protected string $path;

    public function __construct(string $base)
    {
        $this->base = $base;
    }

    public abstract function toString(): string;
}
