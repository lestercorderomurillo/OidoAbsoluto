<?php

namespace Pipeline\FileSystem\Path\Web;

use Pipeline\FileSystem\Path\PathBase;
use function Pipeline\Kernel\fatal;

class WebDirectory extends PathBase
{
    public function __construct(string $path = "")
    {
        parent::__construct(__URL__);
        $this->path = $path;

        if ($path != "") {
            if ($path[0] == "/") {
                fatal("Web folder paths cannot start with '/' character.");
            }

            if (substr($path, -1) != "/") {
                fatal("Web folder paths must end with '/' character.");
            }
        }
    }

    public function toString(): string
    {
        return $this->base . $this->path;
    }
}
