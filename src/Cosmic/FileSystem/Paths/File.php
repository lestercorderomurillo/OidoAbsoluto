<?php

namespace Cosmic\FileSystem\Paths;

use Cosmic\FileSystem\Bootstrap\BasePath;
use Cosmic\FileSystem\Exceptions\IOException;

/**
 * This class represents an file path. All files should have and extension.
 */
class File extends BasePath
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
        $matches = [];

        preg_match("/(?<=\.)[A-z0-9]*/", $path, $matches);

        if (isset($matches[0]) && strlen($matches[0]) > 0) {
            $this->extension = $matches[0];
        } else {
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
