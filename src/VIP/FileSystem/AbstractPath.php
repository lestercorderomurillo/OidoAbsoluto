<?php

namespace VIP\FileSystem;

use Psr\Log\LoggerAwareTrait;
use function VIP\Core\Logger;

abstract class AbstractPath
{
    use LoggerAwareTrait;

    protected string $base;
    protected string $path;

    public function __construct(string $base)
    {
        $this->base = $base;
        $this->setLogger(Logger());
    }

    public abstract function toString(): string;
}
