<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\HTTP\Providers;

use Cosmic\Core\Abstracts\AutoProvider;
use Cosmic\Core\Session;
use Cosmic\HTTP\Request;
use Cosmic\HTTP\Router;

/**
 * This class represents a HTTP server. It's a controller around the router and the process of loading the routes.
 */
class HttpServerProvider extends AutoProvider
{
    private static Request $request;
    
    /**
     * @inheritdoc
     */
    public static function boot(): void
    {
        static::$request = Request::intercept();
    }

    /**
     * @inheritdoc
     */
    public static function provide()
    {
        app()->primitive(Request::class, static::$request);
        app()->singleton(Session::class);
        app()->singleton(Router::class);
    }
}
