<?php

namespace Pipeline\FileSystem\Path\Web;

use Pipeline\FileSystem\Path\PathBase;
use Pipeline\Utilities\Text;
use function Pipeline\Kernel\fatal;

class WebPath extends PathBase
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
            fatal("WebPath pathString cannot start or end with '/' character.");
        }
    }

    public function toString(): string
    {
        $base = $this->base;
        if (Text::startsWith($this->path, "https")) {
            $base = "";
        }

        if ($this->extension == "") {
            return $base . $this->path;
        } else {
            return $base . $this->path . ".$this->extension";
        }
    }
}
