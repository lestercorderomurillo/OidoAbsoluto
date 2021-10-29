<?php

namespace Pipeline\Utilities;

use Pipeline\PypeEngine\HTML\BodySelection;

class Pattern
{
    public static function findByText(string &$source, string $findme, int $position = 0)
    {
        $position = min($position, strlen($source));
        return strpos($source, $findme, $position);
    }

    public static function selectStringByQuotes(string $source, string $opening = "{", string $closure = "}", int $search_offset = 0, int $after_select_offset = -1): BodySelection
    {
        $search_offset = min($search_offset, strlen($source));

        $start = strpos($source, $opening, $search_offset);
        $end = false;

        if ($start !== false) {
            $cursor = $start + strlen($opening);
            $end = strpos($source, $closure, $cursor);
        }

        if ($after_select_offset == -1) {
            $start += strlen($opening);
        } else {
            $start += $after_select_offset;
        }

        return new BodySelection($start, $end, $source);
    }

    public static function isNumber($val): bool
    {
        if (!isset($val)) return false;
        $int = (int)$val;
        if ((string)$int != (string)$val) {
            throw new \Exception("Integer parse error at: " . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] . " function call");
        }
        return true;
    }
}
