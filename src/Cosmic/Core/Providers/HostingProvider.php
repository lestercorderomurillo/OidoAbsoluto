<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Providers;

use Cosmic\Core\Abstracts\AutoProvider;

class HostingProvider extends AutoProvider
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
        //app()->primitive('__HOST__', confi);
        cout("HostingProvider has finished.");
    }
}
