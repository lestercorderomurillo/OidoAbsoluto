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
use Cosmic\Utilities\Strings;

/**
 * This class represents an file path. All files should have and extension.
 */
class FilePath extends Path
{
    private string $extension;

    /**
     * Constructor. If the path is invalid, IO exception will be thrown.
     * Extensions will be extracted from the path automatically.
     * 
     * @param string $path The path to use.
     * 
     * @return void 
     * @throws IOException On path error.
     */
    public function __construct(string $path)
    {
        parent::__construct();

        $this->extension = substr($path, Strings::lastOcurrence($path, ".") + 1);

        if ($this->extension == null) {
            throw new IOException("The provided path is invalid");
        }

        $this->setPath($path);
    }

    /**
     * Returns the extension of the file. The dot is not included.
     * 
     * @return string The file extension. Ex: php or js.
     */
    public function getExtension(): string
    {
        return $this->extension;
    }
}
