<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\VDOM\Engine;

/**
 * This helper class is used to select substrings inside strings. Extremelly useful when doing string replacements.
 */
class TokenSelector extends Selector
{

    /**
     * Selects the given string, starting at the given position and ending at the other position.
     * 
     * @param string $input The string to select.
     * @param int|null $startPosition The initial position of the selection.
     * @param int|null $endPosition The final position of the selection.
     * @param string $startDelimiter The initial delimiter used to select this string. Can be left blank
     * @param $endDelimiter The final delimiter used to select this string. Can be left blank
     * 
     * @return void
     */
    public function __construct(string $input, $startPosition, $endPosition, string $startDelimiter = "{", $endDelimiter = "}")
    {
        parent::__construct($input, $startPosition, $endPosition);
        $this->startDelimiter = $startDelimiter;
        $this->endDelimiter = $endDelimiter;
    }

    /**
     * Get the origin string used.
     * @return string The original string, without the selection.
     */
    public function getSourceString(): string
    {
        return $this->input;
    }

    /**
     * @inheritdoc
     */
    public function toString(): string
    {
        return $this->getString();
    }

    /**
     * Get the resulting string after performing the selection.
     * 
     * @return string The "cutted" string. 
     */
    public function getString(bool $excludeDelimiters = true): string
    {
        if ($excludeDelimiters) {
            $startPosition = $this->startPosition + strlen($this->startDelimiter);

            if($this->endPosition != null){
                $endPosition = $this->endPosition - strlen($this->endDelimiter);
            }else{
                $endPosition = strlen($this->input);
            }

            return trim(substr($this->input, $startPosition, $endPosition - $startPosition));
        }
        return trim(substr($this->input, $this->startPosition, $this->endPosition - $this->startPosition));
    }

    /**
     * Select a string using the given delimiters. Will return on the first match.
     * 
     * @param string $input The input string to check for.
     * @param string $startDelimiter The opening delimiter.
     * @param $endDelimiter The closure delimiter.
     * @param int $offset The starting position to start the searching.
     * @return TokenSelector A new TokenSelector instance.
     */
    public static function findNext(string $input, string $startDelimiter = "{", $endDelimiter = "}", int $offset = 0): TokenSelector
    {
        $offset = min($offset, strlen($input));

        $start = strpos($input, $startDelimiter, $offset);
        $end = false;

        if ($start !== false) {

            if ($endDelimiter != null) {
                $end = strpos($input, $endDelimiter, $start) + 1;
            }else{
                $end = strlen($input);
            }
        }

        return new TokenSelector($input, $start, $end, $startDelimiter, $endDelimiter);
    }
}
