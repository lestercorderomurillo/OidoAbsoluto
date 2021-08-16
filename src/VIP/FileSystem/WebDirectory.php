<?php

namespace VIP\FileSystem;

use function VIP\Core\Logger;

class WebDirectory extends AbstractPath
{
    public function __construct(string $path = "")
    {
        $this->setLogger(Logger());
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
        return __URL__ . $this->path;
    }
}
