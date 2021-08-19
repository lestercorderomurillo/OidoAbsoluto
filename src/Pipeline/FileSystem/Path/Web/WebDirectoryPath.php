<?php

namespace Pipeline\FileSystem\Path\Web;

use Pipeline\FileSystem\Path\AbstractPath;

class WebDirectoryPath extends AbstractPath
{
    public function __construct(string $path = "")
    {
        parent::__construct(__URL__);
        $this->path = $path;

        if ($path != "") {
            if ($path[0] == "/") {
                $this->logger->error("Web folder paths CANNOT start /");
            }

            if (substr($path, -1) != "/") {
                $this->logger->error("Web folder paths MUST end with /");
            }
        }
    }

    public function toString(): string
    {
        return $this->base . $this->path;
    }
}
