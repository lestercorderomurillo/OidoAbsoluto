<?php

namespace Cosmic\Utilities;

class Collection
{
    public static $override = true;

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

    public static function mapKeys(array $array, \Closure $function): array
    {
        $function = new \ReflectionFunction($function);
        $number = $function->getNumberOfParameters();

        foreach ($array as $key => $value) {
            if ($number == 1) {
                $new_key = $function->invoke($key);
            } else if ($number == 2) {
                $new_key = $function->invoke($key, $value);
            }
            unset($array[$key]);
            $array[$new_key] = $value;
        }
        return $array;
    }

    public static function mapValues(array $array, \Closure $function): array
    {
        $function = new \ReflectionFunction($function);
        $number = $function->getNumberOfParameters();

        foreach ($array as $key => $value) {
            if ($number == 1) {
                $new_value = $function->invoke($key);
            } else if ($number == 2) {
                $new_value = $function->invoke($key, $value);
            }
            $array[$key] = $new_value;
        }
        return $array;
    }

    public static function mergeList(...$arrays_to_mix): array
    {
        $result_array = [];
        if (self::$override) {
            foreach ($arrays_to_mix as $array) {
                foreach ($array as $value) {
                    $result_array[] = $value;
                }
            }
        } else {
            foreach ($arrays_to_mix as $array) {
                foreach ($array as $value) {
                    if (!isset($result_array[$value])) {
                        $result_array[] = $value;
                    }
                }
            }
        }


        return $result_array;
    }

    public static function mergeDictionary(...$arrays_to_mix): array
    {
        $result_array = [];

        if (self::$override) {
            foreach ($arrays_to_mix as $array) {
                foreach ($array as $key => $value) {
                    $result_array[$key] = $value;
                }
            }
        } else {
            foreach ($arrays_to_mix as $array) {
                foreach ($array as $key => $value) {
                    if (!isset($result_array[$key])) {
                        $result_array[$key] = $value;
                    }
                }
            }
        }
        return $result_array;
    }

    public static function convertDictionaryToList(array $array): array
    {
        $result_array = [];
        if (!self::is2Dimensional($array)) {
            foreach ($array as $_ => $value) {
                $result_array[] = $value;
            }
        }
        return $result_array;
    }
}
