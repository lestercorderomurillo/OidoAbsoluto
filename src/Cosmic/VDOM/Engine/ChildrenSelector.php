<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\VDOM\Engine;

class ChildrenSelector
{
    public static function findNext(string $html, string $tagName, int $initialOffset)
    {
        $level = 0;
        $offset = $initialOffset;

        while (($htmlStrip = TokenSelector::findNext($html, "<", ">", $offset))->isValid()) 
        {
            $isCloseTag = false;

            if ($html[$htmlStrip->getStartPosition() + 1] == "/") {
                $isCloseTag = true;
                $htmlStrip->moveStartPosition();
            }

            if ((explode(" ", $htmlStrip->getString(true), 2)[0]) == $tagName) {

                if ($isCloseTag) {

                    if ($level == 0) {
                        
                        return new Selector($html, $initialOffset, $htmlStrip->getEndPosition() - strlen("</$tagName>"), "<", ">");

                    } else {
                        $level--;
                    }

                } else {

                    $level++;

                }

            }

            $offset = $htmlStrip->getEndPosition();
        }

        return null;
    }
}
