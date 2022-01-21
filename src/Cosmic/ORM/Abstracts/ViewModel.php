<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\ORM\Abstracts;

/**
 * This class represents a simple view model. Developers should extend this class to make their own models.
 */
abstract class ViewModel
{
    /**
     * When inserting models to the database, if they are a view model instance, they will be rejected,
     * as only models with explicit writed properties are allowed to be inserted into the datasource.
     */
}
