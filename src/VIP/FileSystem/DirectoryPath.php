<?php

namespace VIP\FileSystem;

class DirectoryPath extends AbstractPath
{
    public function __construct(string $base, string $path = "")
    {
        parent::__construct($base);
        $this->path = $path;

        if ($path != "") {
            if ($path[0] == "/") {
                $this->logger->error("Folder paths CANNOT start /");
            }

            if (substr($path, -1) != "/") {
                $this->logger->error("Folder paths MUST end with /");
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
