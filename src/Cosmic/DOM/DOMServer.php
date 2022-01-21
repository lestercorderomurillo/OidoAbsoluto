<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\HTTP\Server;

use Cosmic\FileSystem\FS;
use Cosmic\Core\Interfaces\RunnableInterface;

/**
 * This class represents a HTTP component server. 
 * The framework uses this server to handle requests for components render updates.
 */
class DOMServer implements RunnableInterface
{
    /**
     * @inheritdoc
     */
    public function run(): void
    {
        $files = FS::find(
            ["src/Cosmic/Bundle/Components/", "app/Components/"],
            ["php", "phps", "hphp"]
        );

        foreach ($files as $file) {
            FS::import($file);
        }
    }
}
