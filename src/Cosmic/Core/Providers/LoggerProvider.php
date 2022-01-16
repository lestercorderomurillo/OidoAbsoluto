<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Providers;

use Cosmic\Core\Logger;
use Cosmic\Core\ConsoleLogger;
use Cosmic\Core\Abstracts\AutoProvider;

/**
 * This class represents a HTTP server. It's a controller around the router and the process of loading the routes.
 */
class LoggerProvider extends AutoProvider
{
    /**
     * @inheritdoc
     */
    public static function boot(): void
    {
    }

    /**
     * @inheritdoc
     */
    public static function provide()
    {
        app()->singleton(Logger::class);
        app()->singleton(ConsoleLogger::class);
        cout('LoggingProvider has finished.');
    }
}
