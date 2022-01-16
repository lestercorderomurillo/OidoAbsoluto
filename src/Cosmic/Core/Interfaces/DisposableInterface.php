<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Interfaces;

/**
 * This class represents objects that can be disposed.
 */
interface DisposableInterface
{
    /**
     * Dispose this instance.
     * 
     * @return void
     */
    public function  __destruct();
}
