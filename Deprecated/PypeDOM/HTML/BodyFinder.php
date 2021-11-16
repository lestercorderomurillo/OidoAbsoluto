<?php

namespace Pipeline\PypeDOM\HTML;

use Pipeline\Utilities\Pattern;

class BodyFinder
{
    public static function detectBody(string &$input, string $tag, int $initial_offset = 0): ?Selection
    {
        $level = 0;
        $offset = $initial_offset;

        while (($html_strip = Pattern::selectStringByQuotes($input, "<", ">", $offset, 1))->isValid()) {

            $closure = false;

            if ($input[$html_strip->getStartPosition()] == "/") {
                $closure = true;
                $html_strip->moveStartPosition();
            }

            if ((explode(" ", $html_strip->getString(), 2)[0]) == $tag) {
                if ($closure) {
                    if ($level == 0) {
                        return new Selection($initial_offset, $html_strip->getEndPosition() - strlen("</$tag"), $input);
                    } else {
                        $level--;
                    }
                } else {
                    $level++;
                }
            }

            $offset = $html_strip->getEndPosition();
        }

        return null;
    }
}
