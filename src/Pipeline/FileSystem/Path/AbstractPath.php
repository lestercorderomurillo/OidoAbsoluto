<?php

namespace Pipeline\FileSystem\Path;

use Pipeline\Logger\Logger;
use Pipeline\Traits\StringableTrait;
use function Pipeline\Accessors\Dependency;

abstract class AbstractPath
{
    use StringableTrait;
    
    protected string $base;
    protected string $path;

    public function __construct(string $base)
    {
        $this->logger = Dependency(Logger::class);
        $this->base = $base;
    }

    public abstract function toString(): string;
}
