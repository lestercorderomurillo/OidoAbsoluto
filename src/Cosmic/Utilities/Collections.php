<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Utilities;

use Cosmic\Core\Types\JSON;
use Cosmic\Core\Exceptions\SourceException;
use Cosmic\Core\Interfaces\FactoryInterface;
use Cosmic\FileSystem\Paths\FilePath;

/**
 * This utility class provides methods for collection manipulation and encoding.
 */
class Collections implements FactoryInterface
{
    /**
     * Check if the given array is a string key-based collection.
     * 
     * @param array $array The collection to analyze.
     * @return bool True when it's a dictionary, false otherwise.
     */
    public static function isDictionary(array $array): bool
    {
        foreach ($array as $key => $value){
            if (!is_string($key)){
                return false;
            }
        }

        return true;
    }
    
    /**
     * Check if the given array is a number indexed collection.
     * 
     * @param array $array The collection to analyze.
     * @return bool True when it's a list, false otherwise.
     */
    public static function isList(array $array)
    {
        return $array === [] || (array_keys($array) === range(0, count($array) - 1));
    }

    /**
     * Check it the given array is an array collection..
     * 
     * @param array $array The collection to analyze.
     * @return bool True when the array is an array collection, false otherwise.
     */
    public static function isArrayCollection(array $array)
    {
        foreach ($array as $value) {
            if (is_array($value)) return true;
        }

        return false;
    }

    /**
     * Remaps all the keys, executing a closure for all the entries in the given collection.
     * The closure must always return. When the closure has only one argument,
     * the closure will be executed only using key as the first argument,
     * if the closure has two parameters, both key and value will be passed to the closure.
     * 
     * @param array $array The input collection.
     * @param \Closure $closure The closure to use for each key.
     * @return array The  re-mapped collection. Can be either a list or a dictionary.
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
     * Remaps all the values, executing a closure for all the entries in the given collection.
     * The closure must always return. When the closure has only one argument,
     * the closure will be executed only using key as the first argument,
     * if the closure has two parameters, both key and value will be passed to the closure.
     * 
     * @param array $array The input collection.
     * @param \Closure $closure The closure to use for each value.
     * @return array The re-mapped collection. Can be either a list or a dictionary array.
     */
    public static function mapValues(array $array, \Closure $closure): array
    {
        $closure = new \ReflectionFunction($closure);
        $number = $closure->getNumberOfParameters();

        foreach ($array as $key => $value) {

            if ($number == 1) {
                $newValue = $closure->invoke($value);
            } else if ($number == 2) {
                $newValue = $closure->invoke($key, $value);
            }

            $array[$key] = $newValue;
        }

        return $array;
    }

    /**
     * Perform a merge from all of the given array arguments.
     * Will merge both dictionaries and lists, storing entries without keys as number indexed by default.
     * When hinted, this method will optimize the merge algorithm.
     * 
     * @param array[] $arrays A list of dictionaries or lists to merge.
     * @return array The output array.
     */
    public static function merge(...$arrays): array
    {
        $outputArray = [];

        $dictionaryHinted = ($arrays[0] === "Dictionary") ? true : false;
        $listHinted = ($arrays[0] === "List") ? true : false;

        foreach ($arrays as $array) {
            if ($dictionaryHinted || static::isDictionary($array)){
                foreach ($array as $key => $value) {
                    $outputArray[$key] = $value;
                }
            }else if ($listHinted || static::isList($array)){
                foreach ($array as $value) {
                    $outputArray[] = $value;
                }
            }
        }

        return $outputArray;
    }

    /**
     * Perform a unique merge from tht given array arguments. 
     * Will merge both dictionaries and lists, storing values without keys as number indexed.
     * 
     * @param array[] $arrays A list of dictionaries or lists to merge.
     * 
     * @return array The output array.
     */
    public static function uniqueMerge(...$arrays): array
    {
        $outputArray = [];

        foreach ($arrays as $array) {
            if (static::isDictionary($array)){
                foreach ($array as $key => $value) {
                    if(!isset($outputArray[$key])){
                        $outputArray[$key] = $value;
                    }
                }
            }else if (static::isList($array)){
                foreach ($array as $value) {
                    if(!isset($outputArray[$value])){
                        $outputArray[] = $value;
                    }
                }
            }
        }

        return $outputArray;
    }

    // unique merge

    /**
     * Normalize the given data if it's not already, to an array.
     * If it's not, create a new list and put this entry as the first position.
     * 
     * @param mixed|array $data The data to be normalized.
     * @return array The array normalized. Starts with index 0.
     */
    public static function normalizeToList($data): array
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
     * @param File|string $file The file to read from. 
     * If provided a string, a new file handler will be created for you.
     * @return array The JSON object converted to array.
     * @throws SourceException When the type is not recognized.
     */
    public static function from($file)
    {
        if(is_string($file)){
            $file = new FilePath($file);
        }

        switch($file->getExtension()){
            case "json": return JSON::from($file)->toArray(); break;
        }

        throw new SourceException("Cannot automatically convert the data to an array.");
    }

    /**
     * Return the first element stored in the collection.
     * 
     * @param array $data The collection to extract the first element from.
     * @return mixed The entry at the first position in the array.
     */
    public static function getFirstElement(array $data)
    {
        return $data[0];
    }

    /**
     * Return the last element stored in the collection.
     * 
     * @param array $data The collection to extract the last element from.
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
     * @deprecated
     */
    public static function tokenize(string $base = __EMPTY__, $input = []): array
    {
        $tokens = [];

        if(self::isArrayCollection($input)){

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
     * @deprecated
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
                $tokens = Collections::merge($tokens,$recursiveTokens);

                $tokens[$tokenName] = Collections::encodeArraytoString($value);
            }
        }

        return $tokens;
    }

    /**
     * Check if the given array contains only elements of the given type.
     * 
     * @param string $className The classname to match for.
     * @param array $array The collection to verify.
     * @return bool True when the array contains only elements of the given type, false otherwise.
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

    /**
     * Encode the given collection in a string representation.
     * Requires scaping to distinguish between other kinds of strings.
     * 
     * @param array $array The array to encode.
     * @param string $encodeKey The starting string to look for.
     * @return string The stringfy array representation of the given array.
     */
    public static function encodeArraytoString(array $array, string $encodeKey = "@ARR"): string
    {
        $json = json_encode($array);
        return $encodeKey . Transport::encodeBase64SafeURL($json);
    }

    /**
     * Convert the given string representation of an array back to an string.
     * 
     * @param string $string The string to decode.
     * @param string $decodeKey The starting string to look for.
     * @return array The decoded array.
     */
    public static function decodeStringToArray(string $string, string $decodeKey = "@ARR"): array
    {
        if(str_starts_with($string, $decodeKey)){
            $string = substr($string, strlen($decodeKey));
        }

        return json_decode(Transport::decodeBase64SafeURL($string), true);
    }
}
