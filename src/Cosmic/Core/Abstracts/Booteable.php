<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Abstracts;

/**
 * This class represents objects that can be booted.
 */
abstract class Booteable extends Disposable
{
    /**
     * Will execute the boot method()
     * 
     * @return void
     */
    function __constructor()
    {
        $reflectionClass = new \ReflectionClass($this);
        
        if ($reflectionClass->hasMethod('boot')){
            $callable = "boot";
            $this->$callable();
        }
    }
    
}
