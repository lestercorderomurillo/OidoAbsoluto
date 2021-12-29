<?php

namespace Cosmic\HTTP\Server;

use Cosmic\Binder\DOM;
use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\File;
use Cosmic\FileSystem\Paths\Folder;
use Cosmic\HTTP\Interfaces\ServerInterface;
use function Cosmic\Core\Bootstrap\app;

/**
 * This class represents a HTTP component server. 
 * The framework uses this server to handle requests for components render updates.
 */
class ComponentServer implements ServerInterface
{
    /**
     * @inheritdoc
     */
    public function run(): void
    {
        $files = FileSystem::find(new Folder("src/Cosmic/Bundle/Components/"), ["php", "phps", "phpx"]);
        foreach ($files as $file) {
            FileSystem::import(new File($file));
        }

        /*app()->injectPrimitive("metaBundles", 
        [
            [
                "name" => "timestamp",
                "content" => "1"
            ],
            [
                "name" => "page",
                "content" => "2"
            ]
        ]);*/
    }
}
