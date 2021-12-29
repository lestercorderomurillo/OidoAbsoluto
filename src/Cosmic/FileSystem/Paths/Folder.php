<?php

namespace Cosmic\FileSystem\Paths;

use Cosmic\Utilities\Text;
use Cosmic\FileSystem\Bootstrap\BasePath;
use Cosmic\FileSystem\Exceptions\IOException;

/**
 * This class represents an directory path. All directories paths should end with a slash.
 */
class Folder extends BasePath
{
    /**
     * Constructor. If the directory path is invalid, IO exception will be thrown.
     * 
     * @return void
     * @throws IOException If the directory path does not end with a slash.
     */
    public function __construct(string $folder)
    {
        parent::__construct();
        if (!Text::endsWith($folder, "/") && !Text::endsWith($folder, "\\")) {
            throw new IOException("Folder path must end with a slash");
        }
        
        $this->setPath($folder);
    }
}
