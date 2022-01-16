<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\FileSystem\Paths;

use Cosmic\FileSystem\Abstracts\Path;
use Cosmic\FileSystem\Exceptions\IOException;

/**
 * This class represents an directory path. All directories paths should end with a slash.
 */
class FolderPath extends Path
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
        if (!str_ends_with($folder, "/") && !str_ends_with($folder, "\\")) {
            throw new IOException("Folder path must end with a slash");
        }
        
        $this->setPath($folder);
    }
}
