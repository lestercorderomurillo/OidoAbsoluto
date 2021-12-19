<?php

namespace Cosmic\Utilities;

use Cosmic\Core\Types\JSON;
use Cosmic\FileSystem\Paths\File;

/**
 * This helper class is used to provide methods for array, list and dictionary manipulation.
 */
class Collection
{
    /**
     * @var bool $override When true, this boolean will override when doing merge and replacements.
     * By default, the overriding is enabled (true).
     */
    private static bool $override = true;

    /**
     * Enable merge and replacements override.
     * 
     * @return void 
     */
    public static function enableOverride(): void
    {
        self::$override = true;
    }

    /**
     * Disable merge and replacements override.
     * 
     * @return void 
     */
    public static function disableOverride(): void
    {
        self::$override = false;
    }

    /**
     * Check it the given array is multi-dimensional.
     * 
     * @param array $array The collection to check.
     * 
     * @return bool True if it is, false otherwise.
     */
    public static function isMultidimensional(array $array)
    {
        $rv = array_filter($array, 'is_array');
        if (count($rv) > 0) return true;
        return false;
    }

    /**
     * Check it the given array is 2-dimensional.
     * 
     * @param array $array The collection to check.
     * 
     * @return bool True if it is, false otherwise.
     */
    public static function is2Dimensional(array $array)
    {
        foreach ($array as $value) {
            if (is_array($value)) return true;
        }
        return false;
    }

    /**
     * Check it the given array is 3-dimensional.
     * 
     * @param array $array The collection to check.
     * 
     * @return bool True if it is, false otherwise.
     */
    public static function is3Dimensional(array $array)
    {
        $count = count($array);
        for ($i = 0; $i < $count; $i++) {
            if (is_array($array[$i])) return true;
        }
        return false;
    }

    /**
     * Executes a closure for all the keys in this collection and returns them as an new one.
     * 
     * @param array $array The collection to execute the closure.
     * @param \Closure $closure The closure to use for each key.
     * 
     * @return array[]|array The resulting collection. Can be either a list or a dictionary.
     */
    public static function mapKeys(array $array, \Closure $closure): array
    {
        $closure = new \ReflectionFunction($closure);
        $number = $closure->getNumberOfParameters();

        foreach ($array as $key => $value) {

            if ($number == 1) {

                $newKey = $closure->invoke($key);
            } else if ($number == 2) {

                $newKey = $closure->invoke($key, $value);
            }

            unset($array[$key]);
            $array[$newKey] = $value;
        }
        return $array;
    }

    /**
     * Executes a closure for all the values in this collection and returns them as an new one.
     * 
     * @param array $array The collection to execute the closure.
     * @param \Closure $closure The closure to use for each value.
     * 
     * @return array[]|array The resulting collection. Can be either a list or a dictionary.
     */
    public static function mapValues(array $array, \Closure $closure): array
    {
        $closure = new \ReflectionFunction($closure);
        $number = $closure->getNumberOfParameters();

        foreach ($array as $key => $value) {

            if ($number == 1) {

                $newValue = $closure->invoke($key);
            } else if ($number == 2) {

                $newValue = $closure->invoke($key, $value);
            }

            $array[$key] = $newValue;
        }

        return $array;
    }

    /**
     * Merge multiple lists into a single one.
     * 
     * @param array[] $arrays A list of lists to merge.
     * 
     * @return array[] The resulting list from the merge. (1D array with values)
     */
    public static function mergeList(...$arrays): array
    {
        $result_array = [];
        if (self::$override) {
            foreach ($arrays as $array) {
                foreach ($array as $value) {
                    $result_array[] = $value;
                }
            }
        } else {
            foreach ($arrays as $array) {
                foreach ($array as $value) {
                    if (!isset($result_array[$value])) {
                        $result_array[] = $value;
                    }
                }
            }
        }


        return $result_array;
    }

    /**
     * Merge multiple dictionaries into a single one.
     * 
     * @param array[] $arrays A list of dictionaries to merge.
     * 
     * @return array[] The resulting dictionary from the merge. (2D array with keys and values)
     */
    public static function mergeDictionary(...$arrays): array
    {
        $result_array = [];

        if (self::$override) {
            foreach ($arrays as $array) {
                foreach ($array as $key => $value) {
                    $result_array[$key] = $value;
                }
            }
        } else {
            foreach ($arrays as $array) {
                foreach ($array as $key => $value) {
                    if (!isset($result_array[$key])) {
                        $result_array[$key] = $value;
                    }
                }
            }
        }
        return $result_array;
    }

    /**
     * Convers a 2D array (aka Dictionary) to a simple indexed list. (aka Collection)
     * 
     * @param array $array The 2D dictionary-array to convert to 1D collection-array.
     * 
     * @return array A simple collection. (1D array)
     */
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

    /**
     * Normalize an array if its not already. If it's already an array, just return it. 
     * If it's not, create a new array and put this element as the front of the collection.
     * 
     * @param mixed|array $data The data to be normalized.
     * 
     * @return array The array normalized.
     */
    public static function normalize($data): array
    {
        if (!is_array($data)) {
            $data = [$data];
        }
        return $data;
    }

    /**
     * Create a new array collection directly from a file.
     * The returned array can be a list or a dictionary.
     * 
     * @param File $data The data to be normalized.
     * 
     * @return array The JSON object converted to array.
     */
    public static function from(File $file): array
    {
        return JSON::from($file)->toArray();
    }
}
