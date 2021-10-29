<?php

namespace Pipeline\FileSystem\Path\Local;

use Pipeline\FileSystem\Path\PathBase;
use Pipeline\FileSystem\Path\Web\WebDirectory;

class DirectoryPath extends PathBase
{
    public function __construct(string $base, string $path = "")
    {
        parent::__construct($base);
        $this->path = $path;

        if ($path != "") {
            if ($path[0] == "/") {
                $this->logger->error("Folder paths CANNOT start / on $base : $path");
            }

            if (substr($path, -1) != "/") {
                $this->logger->error("Folder paths MUST end with / on $base : $path");
            }
        }
    }

    public function getBase(): string
    {
        return $this->base;
    }

    public function toString(): string
    {
        return $this->base . $this->path;
    }

    public function toWebDirectory(): WebDirectory
    {
        $reroute = str_replace(__ROOT__, "", $this->toString());
        return new WebDirectory($reroute);
    }
}
