<?php

namespace Cosmic\HPHP\Engine\Selectors;

use Cosmic\HPHP\Engine\Selection;

class ChildrenSelector
{
    public static function findNext(string $input, string $selectorTagName, int $readOffset)
    {
        $nodeDepth = 0;

        $writeOffset = $readOffset;

        while (($elementSelection = TokenSelector::findNext($input, "<", ">", $writeOffset))->isValid()) {

            $elementString = trim($elementSelection->toString());

            if ($selectorTagName == '') {


                if ($elementString == '') {

                    $elementHasChildren = true;
                    $isClosure = false;
                    $tagName = '';
                } else if ($elementString == '/') {

                    $elementHasChildren = false;
                    $isClosure = true;
                    $tagName = '';
                }
            } else {
                $elementHasChildren = (substr($elementString, -1) != '/');

                $elementString = (!$elementHasChildren) ? substr($elementString, 0, -1) : $elementString;

                $out = explode(' ', $elementString, 2);

                $tagName = $out[0];

                $isClosure = false;
                if ($tagName[0] == "/") {
                    $isClosure = true;
                    $tagName = substr($tagName, 1);
                }
            }

            if ($tagName == $selectorTagName) {
                if ($isClosure) {

                    if ($nodeDepth == 0) {

                        return new Selection($input, $readOffset, $elementSelection->getLastPosition() - strlen("</$tagName>") + 1);
                    } else {

                        $nodeDepth--;
                    }
                } else {


                    $nodeDepth++;
                }
            }

            $writeOffset = $elementSelection->getLastPosition();
        }
        return null;
    }
}
