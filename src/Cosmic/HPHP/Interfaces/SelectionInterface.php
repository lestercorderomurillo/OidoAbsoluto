<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\HPHP\Interfaces;

interface SelectionInterface
{
    /**
     * @return int Return the first position of the selection.
     */
    public function getFirstPosition(): int;

    /**
     * @return mixed Return the first position variable reference.
     */
    public function &getFirstPositionHandler();

    /**
     * @return int|false Return the last position of the selection.
     * Will return false if no last position is available.
     */
    public function getLastPosition();

    /**
     * @return mixed Return the last position variable reference.
     */
    public function &getLastPositionHandler();

    /**
     * @return string Return the original string without modifications.
     */
    public function getParentString(): string;

    /**
     * Check if the current selection is still valid.
     * @return bool True if the selection is valid, false otherwise.
     */
    public function isValid(): bool;
}
