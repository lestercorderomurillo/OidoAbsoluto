<?php

namespace Cosmic\FileSystem;

use Cosmic\Binder\Compiler;
use Cosmic\Utilities\Collection;
use Cosmic\FileSystem\Bootstrap\BasePath;
use Cosmic\FileSystem\Exceptions\IOException;
use Cosmic\FileSystem\Paths\Folder;
use Cosmic\FileSystem\Paths\File;

/**
 * This class represents an abstraction for the filesystem of your OS. Developers can
 * load, write and read files and directories very easily using the path classes.
 */
class FileSystem
{

    /**
     * Search all files within a directory. Can be used with an specific extension.
     * The search will convert all paths to their public web variant.
     * If the extension is php, this will throw an exception.
     * 
     * @param Folder $folder The directory path to search.
     * @param string $extension Can be used as filters. By default all extensions. (*)
     * 
     * @return string[] A collection of paths.
     * @throws IOException When trying to use a php file extension.
     */
    public static function URLFind(Folder $folder, string $extension = "*"): array
    {
        if (($extension = strtolower($extension)) == "php") {
            throw new IOException("URL find cannot be used with PHP extension for security reasons");
        }

        return FileSystem::toWebPaths(FileSystem::find($folder, $extension));
    }

    /**
     * Search all files within a directory. Can be used with an specific extension.
     * 
     * @param Folder $folder The directory path to search.
     * @param string[]|string $extensions Can be used as filters. By default all extensions. (*)
     * When passed an array, it can search multiple extensions.
     * 
     * @return File[] A collection of files path.
     */
    public static function find(Folder $folder, $extensions = "*"): array
    {
        $extensions = Collection::normalize($extensions);

        $paths = [];

        foreach ($extensions as $extension) {
            $searchPath = $folder . "*.$extension";

            foreach (FileSystem::recursiveGlob($searchPath) as $file) {
                $paths[] = new File($file);
            }
        }

        return $paths;
    }

    /**
     * Import the module. If the file uses a global return statement, this function will return its value.
     * Otherwise, this function will thrown an exception.
     * 
     * @param File $file The file path to import.
     * @param bool $required If true, an error will be thrown if the file cannot be imported. By default, it's true.
     * @param bool $once The path is remembered. No subsequents imports can be done to this file again.
     * 
     * @return mixed The result can be of any type.
     * @throws IOException When the file doesn't exist.
     */
    public static function import(File $file, bool $required = true, bool $once = false)
    {
        if (!FileSystem::exists($file)) {
            throw new IOException("Failed to import the requested file: $file");
        }

        if(in_array($file->getExtension(), ['phps', 'phpx'])) {
            $precompiled = app()->get(Compiler::class)->precompileFile($file);
            //die($precompiled);
            return eval($precompiled);
        }

        if ($required) {
            if ($once) {
                return require_once($file);
            }
            return require($file);
        }

        if ($once) {
            return include_once($file);
        }
        return include($file);
    }

    /**
     * Check if a file exists.
     * 
     * @param File|Folder $path The file or directory to check.
     * 
     * @return bool True if the file exists, false otherwise.
     */
    public static function exists(BasePath $path): bool
    {
        return file_exists($path);
    }

    /**
     * Read the contents of a file.
     * 
     * @param File $path The file to check.
     * 
     * @return string|false The content of the file as a string, false on error.
     */
    public static function read(File $file): string
    {
        return file_get_contents($file);
    }

    /**
     * Writes data to a file. Can be used in append mode to avoid overwriting.
     * 
     * @param File $path The file to write.
     * @param string $value The value to put into the file.
     * @param int $mode The writing mode. [optional]
     * 
     * The value of flags can be any combination of the following flags (with some restrictions), joined with the binary OR (|) operator.
     * Flag	Description
     * FILE_USE_INCLUDE_PATH	Search for filename in the include directory. See include_path for more information.
     * FILE_APPEND	If file filename already exists, append the data to the file instead of overwriting it. Mutually exclusive with LOCK_EX since appends are atomic and thus there is no reason to lock.
     * LOCK_EX	Acquire an exclusive lock on the file while proceeding to the writing. Mutually exclusive with FILE_APPEND.
     * 
     * @return int|false The number of bytes written to the file , otherwise returns false.
     * 
     */
    public static function write(File $file, string $value, int $mode = FILE_APPEND)
    {
        if (!file_exists(dirname($file))) {
            if (!mkdir(dirname($file), 0777, true)) {
                return false;
            }
        }

        return file_put_contents($file, $value, $mode);
    }

    /**
     * Converts all passed paths to their local variants.
     * Returns all values stored in an array.
     * 
     * @param mixed|array $paths A collection of paths to convert.
     * 
     * @return string[] A collection of compiled paths.
     */
    public static function toLocalPaths($paths): array
    {
        $output = [];

        $paths = Collection::normalize($paths);

        foreach ($paths as $path) {
            if ($path instanceof BasePath) {
                $path->toLocalPath();
                $output[] = $path->toString();
            }
        }

        return $output;
    }

    /**
     * Converts all passed paths to their web variants.
     * Returns all values stored in an array.
     * 
     * @param mixed|array $paths A collection of paths to convert.
     * 
     * @return string[] A collection of compiled paths.
     */
    public static function toWebPaths($paths): array
    {
        $output = [];

        $paths = Collection::normalize($paths);

        foreach ($paths as $path) {
            if ($path instanceof BasePath) {
                $path->toWebPath();
                $output[] = $path->toString();
            }else if(is_string($path)) {
                $path = new File($path);
                $path->toWebPath();
                $output[] = $path->toString();
            }
        }

        return $output;
    }

    /**
     * Executes a recursive glob using a pattern with flags capability.
     * Returns false on failure.
     * 
     * @param string $pattern The glob pattern to use.
     * @param int $flags Flags to apply to the pattern. [optional]
     * 
     * The value of flags can be any combination of the following flags (with some restrictions), joined with the binary OR (|) operator.
     * Valid flags: GLOB_MARK - Adds a slash to each directory returned 
     * GLOB_NOSORT - Return files as they appear in the directory (no sorting). When this flag is not used, the pathnames are sorted alphabetically 
     * GLOB_NOCHECK - Return the search pattern if no files matching it were found 
     * GLOB_NOESCAPE - Backslashes do not quote metacharacters GLOB_BRACE - Expands {a,b,c} to match 'a', 'b', or 'c' 
     * GLOB_ONLYDIR - Return only directory entries which match the pattern 
     * GLOB_ERR - Stop on read errors (like unreadable directories), by default errors are ignored.
     * 
     * @return string[]|false A collection of compiled paths. Returns false on failure.
     */
    private static function recursiveGlob(string $pattern, int $flags = 0): array
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, self::recursiveGlob($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }
}
