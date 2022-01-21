<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\HPHP\Providers;

use Cosmic\HPHP\Compiler;
use Cosmic\Core\Abstracts\AutoProvider;
use Cosmic\Core\Interfaces\ExtendedProviderInterface;
use Cosmic\Core\Interfaces\ProviderInterface;

/**
 * This class represents
 */
class CompilerProvider extends AutoProvider implements ExtendedProviderInterface
{
    /**
     * 
     * 
     * @return void
     */
    public static function boot(): void
    {
        app()->singleton(Compiler::class);
    }

    public static function register($dataSource): void
    {

    }

    /**
     * 
     * 
     * @return mixed
     */
    public static function provide()
    {
    }
}
