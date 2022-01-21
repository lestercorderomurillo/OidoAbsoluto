<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\FileSystem\Providers;

use Cosmic\Core\Abstracts\AutoProvider;

/**
 * This class represents a HTTP server. It's a controller around the router and the process of loading the routes.
 */
class FileSystemProvider extends AutoProvider
{
    /**
     * @inheritdoc
     */
    public static function boot(): void
    {
        define("__ROOT__", strtr(dirname(__DIR__, 4) . "\\", ["\\" => DIRECTORY_SEPARATOR]));
        define("__CONTENT__", __ROOT__ . "Content" . DIRECTORY_SEPARATOR);
    }

    /**
     * @inheritdoc
     */
    public static function provide()
    {
        //cout("Filesystem provider has finished.");
    }
}
