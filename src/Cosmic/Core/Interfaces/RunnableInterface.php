<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Interfaces;

/**
 * This class represents a simple runnable server.
 */
interface RunnableInterface
{
    /**
     * Run the server. This server can request dependencies in it's constructor without inconvenients.
     * 
     * @return int Exit Code
     */
    public function run(): int;
}
