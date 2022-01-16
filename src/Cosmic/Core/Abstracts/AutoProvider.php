<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Abstracts;

use Cosmic\Core\Interfaces\ProviderInterface;

/**
 * This class represents a cosmic application.
 */
abstract class AutoProvider extends Singleton implements ProviderInterface
{
    /**
     * Perform a default provider initialization.
     * Boot and then provide. Other kinds of providers can execute actions in between.
     * 
     * @return static
     */
    public static function default()
    {
        static::boot();
        static::provide();
        return static::instance();
    }
}
