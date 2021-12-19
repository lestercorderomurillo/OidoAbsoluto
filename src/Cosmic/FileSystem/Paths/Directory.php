<?php

namespace Cosmic\FileSystem\Paths;

use Cosmic\Utilities\Text;
use Cosmic\FileSystem\Boot\BasePath;
use Cosmic\FileSystem\Exceptions\IOException;

/**
 * This class represents an directory path. All directories paths should end with a slash.
 */
class Directory extends BasePath
{
    /**
     * Constructor. If the directory path is invalid, IO exception will be thrown.
     * 
     * @return void
     * @throws IOException If the directory path does not end with a slash.
     */
    public function __construct(string $directory_path)
    {
        parent::__construct();
        if (!Text::endsWith($directory_path, "/")) {
            throw new IOException();
        }
        
        $this->setPath($directory_path);
    }
}
