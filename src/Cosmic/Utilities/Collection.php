<?php

namespace Cosmic\Utilities;

use Cosmic\Core\Types\JSON;
use Cosmic\FileSystem\Paths\File;
use Cosmic\ORM\Bootstrap\Model;

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
     * Check it the given array is 1-dimensional.
     * 
     * @param array $array The collection to check.
     * 
     * @return bool True if it is, false otherwise.
     */
    public static function isList(array $array)
    {
        return $array === [] || (array_keys($array) === range(0, count($array) - 1));
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
     * @return array The resulting list from the merge. (1D array with values)
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
     * @return array The resulting dictionary from the merge. (2D array with keys and values)
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
     * Normalize an array to a single element if the collection contains only one element.
     * If not, then return the same array without modifications.
     * 
     * @param mixed|array|null $data The data to be single normalize.
     * 
     * @return mixed|array|null The array or the single element.
     */
    public static function singleNormalize($data)
    {
        if($data == null) return null;
        return (count($data) == 1) ? $data[array_key_first($data)] : $data;
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

    /**
     * Return the first element stored in the collection.
     * 
     * @param array $data The collection.
     * 
     * @return mixed The entry at the last position in the array.
     */
    public static function getFirstElement(array $data)
    {
        return $data[0];
    }

    /**
     * Return the last element stored in the collection.
     * 
     * @param array $data The collection.
     * 
     * @return mixed The entry at the last position in the array.
     */
    public static function getLastElement(array $data)
    {
        $lastElement = end($data);
        reset($data);
        return $lastElement;
    }
    
    /**
     * Explore deep down the collection, and generate all the available tokens for this array. 
     * 
     * @param string $base The base token for the recursive search.
     * @param array $input The collection to tokenize.
     * 
     * @return mixed The tokenized collection.
     */
    public static function tokenize(string $base = __EMPTY__, $input = []): array
    {
        $tokens = [];

        if(self::is2Dimensional($input)){

            foreach ($input as $array) {
                $tokens[] = self::recursiveTokenize($base, $array);
            }

        }else{

            $tokens = self::recursiveTokenize($base, $input);
            
        }

        return $tokens;
    }

    /**
     * [Recursive private function of createTokens]
     * Explore deep down the collection, and generate all the available tokens for this array. 
     * 
     * @param string $base The base token for the recursive search.
     * @param mixed $data The collection to tokenize.
     * 
     * @return mixed The tokenized collection.
     */
    private static function recursiveTokenize(string $base, $data): array
    {
        $tokens = [];

        foreach ($data as $key => $value) {

            $tokenName = ($base != __EMPTY__) ? "$base.$key" : "$key";

            if (is_string($value) || is_int($value) || is_float($value)) {

                $tokens[$tokenName] = $value;

            } else if (is_array($value)) {

                $recursiveTokens = self::recursiveTokenize($tokenName, $value);
                $tokens = Collection::mergeDictionary(
                    $tokens,
                    $recursiveTokens
                );

                $tokens[$tokenName] = Transport::arrayToString($value);
            }
        }

        return $tokens;
    }

    /**
     * Check if the given array contains only elements of the given type.
     * 
     * @param string $className The classname to use.
     * @param array $array The collection to verify.
     * 
     * @return bool True if the given array contains only elements of the given type, false otherwise.
     */
    public static function typeOf(string $className, array $array): bool
    {
        foreach ($array as $value) {
            if(!$value instanceof $className){
                return false;
            }
        }

        return true;
    }
}
