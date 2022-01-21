<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Traits;

/**
 * This trait allows classes to set their class attributes from a conventional array.
 */
trait ValuesGetterTrait
{
    use ClassAwareTrait;

    /**
     * Return the current object stored values.
     * Will only return values already defined in the source class.
     * 
     * @return array The values of this object.
     */
    public function getValues(): array
    {
        return $this->getPublicProperties();
    }

    /**
     * Return the current runtime object stored values.
     * Can return all values, including those who are not defined in the source class.
     * 
     * @return array The values of this object.
     */
    public function getRuntimeValues(): array
    {
        return get_object_vars($this);
    }
}
