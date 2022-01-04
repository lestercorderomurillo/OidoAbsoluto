<?php

namespace Cosmic\FileSystem\Bootstrap;

use Cosmic\Traits\StringableTrait;

/**
 * The base class for all kinds of paths. Can be either a directory or a file.
*/
abstract class BasePath
{
    use StringableTrait;

    private string $path;
    private string $base;
    private string $separator;

    /**
     * Constructor. 
     * 
     * @return void
     */
    public function __construct()
    {
        $this->toLocalPath();
    }

    /**
     * Returns the base root path for the given path.
     * 
     * @return string Tthe base path used.
     */
    public function getBasePath(): string
    {
        return $this->base;
    }

    /**
     * Converts the path to a web path.
     * 
     * @return static
     */
    public function toWebPath()
    {
        $this->separator = "/";
        $this->base = app()->getHostString();
        return $this;
    }

    /**
     * Converts the path to a local path.
     * 
     * @return static
     */
    public function toLocalPath()
    {
        $this->separator = DIRECTORY_SEPARATOR;
        $this->base = app()->getRootFolderString();
        return $this;
    }

    /**
     * Sets the path.
     * 
     * @return void
     */
    public function setPath(string $path): void
    {
        $this->path = str_replace(app()->getRootFolderString(), "", $path);
    }

    /**
     * Returns the path compiled to string form.
     * 
     * @return string The compiled path.
     */
    public function toString(): string
    {
        return $this->cure($this->base . $this->path);
    }

    /**
     * Cure the output. Internally, this method performs some regex replacements.
     * 
     * @return string The cured output.
     */
    public function cure(string $input): string
    {
        return preg_replace("~[\\\/]~", $this->separator, $input);
    }
}
