<?php

namespace VIP\FileSystem;

use function VIP\Core\Logger;

class WebPath extends AbstractPath
{
    private string $extension;

    public function __construct(string $path, string $extension = "")
    {
        $this->path = $path;
        $this->extension = $extension;
        $this->setLogger(Logger());

        if ($path[0] == "/" || substr($path, -1) == "/") {
            $this->logger->error("Web paths CANNOT start or end with /");
        }
    }

    public function toString(): string
    {
        if ($this->extension == "") {
            return __URL__ . $this->path;
        } else {
            return __URL__ . $this->path . ".$this->extension";
        }
    }
}
