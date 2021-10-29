<?php

namespace Pipeline\FileSystem\Path\Local;

use Pipeline\FileSystem\Path\Web\WebPath;

use function Pipeline\Kernel\fatal;

class Path extends LocalPathBase
{
    private string $extension;

    public static function create(string $base, string $path, string $extension)
    {
        $instance = new Path($base, $path, $extension);
        return $instance;
    }

    public function __construct(string $base, string $path, string $extension)
    {
        parent::__construct($base);
        $this->path = $path;
        $this->extension = $extension;

        if ($path[0] == "/" || substr($path, -1) == "/") {
            fatal("File pathString cannot start or end with '/' character.");
        }
    }

    public function toString(): string
    {
        return $this->base . $this->path . ".$this->extension";
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function toWebPath(): WebPath
    {
        $reroute = str_replace(__ROOT__, "", $this->toString());
        $reroute = str_replace("." . $this->extension, "", $reroute);
        return new WebPath($reroute, $this->extension);
    }
}
