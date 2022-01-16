<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Interfaces;

/**
 * This class represents a provider static object. Based on their input, they can generate new data.
 */
interface ExtendedProviderInterface extends ProviderInterface
{
    /**
     * Register new information for this provider instance.
     * 
     * @param mixed $dataSource The data to analyze.
     * @return void
     */
    public static function register($dataSource): void;
}
