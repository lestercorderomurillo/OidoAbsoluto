<?php

namespace Pipeline\Utilities;

class ArrayHelper
{
    public static function isMultidimensional(array $array)
    {
        $rv = array_filter($array, 'is_array');
        if (count($rv) > 0) return true;
        return false;
    }

    public static function is2Dimensional(array $array)
    {
        foreach ($array as $v) {
            if (is_array($v)) return true;
        }
        return false;
    }

    public static function is3Dimensional(array $array)
    {
        $c = count($array);
        for ($i = 0; $i < $c; $i++) {
            if (is_array($array[$i])) return true;
        }
        return false;
    }

    public static function mergeNamedValues(...$arrays_to_mix)
    {
        $result_array = [];
        foreach ($arrays_to_mix as $array) {
            //if (self::isMultidimensional($array)) {
            foreach ($array as $key => $value) {
                $result_array[$key] = $value;
            }
            //}
        }

        return $result_array;
    }

    public static function stackLines(...$arrays_to_mix)
    {
        $result_array = [];
        foreach ($arrays_to_mix as $array) {
            if (!self::isMultidimensional($array)) {
                foreach ($array as $value) {
                    $result_array[] = $value;
                }
            }
        }

        return $result_array;
    }

    public static function convert2DTo1D(array $array)
    {
        $result_array = [];
        if (!self::is2Dimensional($array)) {
            foreach ($array as $_ => $value) {
                $result_array["$value"];
            }
        }
        return $result_array;
    }

    public static function placeholderCreateArray(array $array): array
    {
        $prepare = [];
        $values = [];
        foreach ($array as $key => $value) {
            $prepare[$key] = "`$key` = :$key";
            $values[":$key"] = $value;
        }
        return [$prepare, $values];
    }

    public static function parameterReplace($array_or_string, array $replacers, string $match_start = "{", string $match_end = "}")
    {
        if (is_array($array_or_string)) {
            $replaced_array = $array_or_string;
        } else if (is_string($array_or_string)) {
            $replaced_array = [$array_or_string];
        }
        if (isset($replacers)) {
            foreach ($replaced_array as $key => $value) {
                // we asume value is thing, but if its "thing.som" thy then $value->thatthing!
                $acumulative_replaced_value = $value;
                foreach ($replacers as $_key => $_value) {
                    $acumulative_replaced_value = str_replace($match_start . $_key . $match_end, $_value, "$acumulative_replaced_value");
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
