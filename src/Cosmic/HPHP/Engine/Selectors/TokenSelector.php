<?php

namespace Cosmic\HPHP\Engine\Selectors;

use Cosmic\HPHP\Engine\Selection;

class TokenSelector
{
    public static function findNext(string $input, string $entryToken, $exitToken, int $readOffset = 0): Selection
    {
        $readOffset = min($readOffset, strlen($input));

        $fp = strpos($input, $entryToken, $readOffset);
        $lp = false;

        if ($fp !== false) {

            if ($exitToken == null) {
                $lp = Selection::EOS;
            } else {
                $lp = strpos($input, $exitToken, $fp) + 1;
            }
        }

        if ($entryToken != '' && $fp !== false) {
            $fp += strlen($entryToken);
        }

        if ($exitToken != null && $lp !== false) {
            $lp -= (strlen($exitToken));
        }

        return new Selection($input, $fp, $lp);
    }
}
