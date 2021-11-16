<?php

namespace Pipeline\Utilities;

use Pipeline\DOM\HTML\Selection;

class Pattern
{
    public static function findByText(string &$source, string $findme, int $position = 0)
    {
        $position = min($position, strlen($source));
        return strpos($source, $findme, $position);
    }

    public static function selectStringByQuotes(string $source, string $opening = "{", string $closure = "}", int $search_offset = 0, int $after_select_offset = -1): Selection
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

        return new Selection($start, $end, $source);
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

    public static function substituteTokens($array_or_string, array $replacers, string $match_start = "{", string $match_end = "}", bool $ignore_arrays = false)
    {
        if (is_array($array_or_string)) {
            $replaced_array = $array_or_string;
        } else if (is_string($array_or_string)) {
            $replaced_array = [$array_or_string];
        }
        if (isset($replacers)) {
            foreach ($replaced_array as $key => $value) {
                $acumulative_replaced_value = $value;

                if (Collection::is2Dimensional($replacers)) {
                    foreach ($replacers as $_key => $_value) {
                        foreach ($_value as $__key => $__value) {
                            if ($ignore_arrays) {
                                if (!is_array($__value)) {
                                    $acumulative_replaced_value = str_replace($match_start . $__key . $match_end, $__value, "$acumulative_replaced_value");
                                }
                            } else {
                                $acumulative_replaced_value = str_replace($match_start . $__key . $match_end, $__value, "$acumulative_replaced_value");
                            }
                        }
                    }
                } else {
                    foreach ($replacers as $_key => $_value) {
                        if ($ignore_arrays) {
                            if (!is_array($_value)) {
                                $acumulative_replaced_value = str_replace($match_start . $_key . $match_end, $_value, "$acumulative_replaced_value");
                            }
                        } else {
                            $acumulative_replaced_value = str_replace($match_start . $_key . $match_end, $_value, "$acumulative_replaced_value");
                        }
                    }
                }


                $replaced_array["$key"] = $acumulative_replaced_value;
            }
        }

        if (is_array($array_or_string)) {
            return $replaced_array;
        } else if (is_string($array_or_string)) {
            return $replaced_array[0];
        }
    }
}
