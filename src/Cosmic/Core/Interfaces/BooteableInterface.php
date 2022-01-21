<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Interfaces;

/**
 * This class represents instances that can be booteable.
 */
interface BooteableInterface extends DisposableInterface
{
    /**
     * Boot the given instance.
     * 
     * @return void
     */
    public function boot(): void;
}
