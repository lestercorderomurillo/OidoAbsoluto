<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Abstracts;

use Cosmic\Core\Interfaces\DisposableInterface;

/**
 * This class represents objects that can be disposed.
 */
abstract class Disposable implements DisposableInterface
{
    function __destruct()
    {
        $reflectionClass = new \ReflectionClass($this);
        
        if ($reflectionClass->hasMethod('dispose')){
            $callable = "dispose";
            $this->$callable();
        }
    }
}
