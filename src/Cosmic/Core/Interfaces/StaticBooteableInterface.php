<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Interfaces;

/**
 * This class represents instances that can be booteable statically.
 */
interface StaticBooteableInterface
{
    /**
     * Boot the given instance.
     * Should be invoked only once.
     * 
     * @return void
     */
    public static function boot(): void;
}
