<?php

namespace Cosmic\HTTP\Server;

use Cosmic\HTTP\Request;
use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\File;
use Cosmic\HTTP\Interfaces\ServerInterface;

/**
 * This class represents a HTTP server. It's a controller around the router and the process of loading the routes.
 */
class WebServer implements ServerInterface
{
    /**
     * @inheritdoc
     */
    public function run(): void
    {
        // Load the file from the configuration file
        FileSystem::import(new File("app/" . configuration("application.routesFile")));

        // Now handle the request using the registered routes
        app()->get(Router::class)->process(Request::intercept());
    }
}
