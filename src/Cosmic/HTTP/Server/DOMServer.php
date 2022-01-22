<?php

namespace Cosmic\HTTP\Server;

use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\File;
use Cosmic\FileSystem\Paths\Folder;
use Cosmic\HTTP\Interfaces\ServerInterface;

/**
 * This class represents a HTTP component server. 
 * The framework uses this server to handle requests for components render updates.
 */
class DOMServer implements ServerInterface
{
    /**
     * @inheritdoc
     */
    public function run(): void
    {
        $files = FileSystem::find(
            [
                new Folder("src/Cosmic/Bundle/Components/"),
                new Folder("app/Components/")
            ],
            [
                "php", "phps", "phpx"
            ]
        );

        foreach ($files as $file) {
            FileSystem::import(new File($file));
        }
    }
}
