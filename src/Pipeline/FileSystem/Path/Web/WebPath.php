<?php

namespace Pipeline\FileSystem\Path\Web;

use Pipeline\FileSystem\Path\AbstractPath;

class WebPath extends AbstractPath
{
    private string $extension;

    public static function create(string $string_input): WebPath
    {
        return new WebPath(str_replace(__ROOT__, "", $string_input));
    }

    public function __construct(string $path, string $extension = "")
    {
        parent::__construct(__URL__);
        $this->path = $path;
        $this->extension = $extension;

        if ($path[0] == "/" || substr($path, -1) == "/") {
            $this->logger->error("Web paths CANNOT start or end with /");
        }
    }

    public function toString(): string
    {
        if ($this->extension == "") {
            return $this->base . $this->path;
        } else {
            return $this->base . $this->path . ".$this->extension";
        }
    }
}
