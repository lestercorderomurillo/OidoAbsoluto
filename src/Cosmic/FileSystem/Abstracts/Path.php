<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\FileSystem\Abstracts;

use Cosmic\Traits\StringableTrait;

/**
 * The base class for all kinds of paths. Can be either a directory or a file.
 */
abstract class Path
{
    use StringableTrait;

    /** @var string $path The given path. */
    private string $path;

    /** @var string $base The base root path for the given path. */
    private string $base;

    /** @var string $separator The OS specific path separator. */
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
     * @return string The base path used.
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
        $this->base = configuration("application.host");
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
        $this->base = __ROOT__;
        return $this;
    }

    /**
     * Sets the path.
     * 
     * @return void
     */
    public function setPath(string $path): void
    {
        $this->path = strtr($path, [__ROOT__ => ""]);
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
        return strtr($input, ["\\" => $this->separator, "/" => $this->separator]);
    }
}
