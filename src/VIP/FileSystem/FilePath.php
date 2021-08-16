<?php

namespace VIP\FileSystem;

class FilePath extends AbstractLocalPath
{
    private string $extension;

    public function __construct(string $base, string $path, string $extension)
    {
        parent::__construct($base);
        $this->path = $path;
        $this->extension = $extension;

        if ($path[0] == "/" || substr($path, -1) == "/") {
            $this->logger->error("File paths CANNOT start or end with / on $base : $path");
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
        return new WebPath($reroute, $this->extension);
    }
}
