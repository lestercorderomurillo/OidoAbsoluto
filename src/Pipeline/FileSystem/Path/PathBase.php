<?php

namespace Pipeline\FileSystem\Path;

use Pipeline\Trace\Logger;
use Pipeline\Traits\StringableTrait;
use function Pipeline\Kernel\dependency;

abstract class PathBase
{
    use StringableTrait;
    
    protected string $base;
    protected string $path;

    public function __construct(string $base)
    {
        $this->logger = dependency(Logger::class);
        $this->base = $base;
    }

    public abstract function toString(): string;
}
