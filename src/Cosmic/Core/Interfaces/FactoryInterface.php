<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Interfaces;

/**
 * This class represents factories who can implement the from method.
 */
interface FactoryInterface
{
    /**
     * Create a new in-memory resource from the given data source.
     * 
     * @param mixed $dataSource No restrictions.
     * @return mixed No restrictions on the returned value.
     */
    public static function from($dataSource);
}
