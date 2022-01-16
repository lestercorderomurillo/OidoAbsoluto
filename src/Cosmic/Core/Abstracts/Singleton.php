<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Abstracts;

abstract class Singleton
{
    private static $instances = [];

    protected function __construct()
    {
    }

    final public static function instance()
    {
        $calledClass = get_called_class();

        if (!isset(Singleton::$instances[$calledClass])) {
            Singleton::$instances[$calledClass] = new $calledClass();
        }

        return Singleton::$instances[$calledClass];
    }

    final private function __clone()
    {
    }
}
