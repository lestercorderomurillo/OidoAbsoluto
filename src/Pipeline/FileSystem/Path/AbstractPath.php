<?php

namespace Pipeline\FileSystem\Path;

use Pipeline\Trace\Logger;
use Pipeline\Traits\StringableTrait;

use function Pipeline\Navigate\dependency;

abstract class AbstractPath
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
