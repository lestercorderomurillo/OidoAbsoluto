<?php

namespace Cosmic\HTTP\Server;

use Cosmic\HTTP\Request;
use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\File;
use function Cosmic\Core\Boot\app;
use function Cosmic\Core\Boot\configuration;

/**
 * This class represents a HTTP server. It's a controller around the router and the process of loading the routes.
 * @deprecated Will be removed in a future release to mix with the environment class.
 */
class WebServer
{
    /**
     * Run the current WebServer. They require a router dependency to be already injected.
     * 
     * @return void
     */
    public function run(): void
    {
        // Load the file from the configuration file
        FileSystem::import(new File("app/" . configuration("application.routesFile")));

        // Now handle the request using the registered routes
        app()->get(Router::class)->process(Request::intercept());
    }
}
