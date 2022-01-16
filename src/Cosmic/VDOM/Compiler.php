<?php

namespace Cosmic\VDOM;

use Cosmic\VDOM\Engine\ChildrenSelector;
use Cosmic\VDOM\Engine\Selector;
use Cosmic\VDOM\Engine\TokenSelector;

class Compiler
{
    public static function compilePHPXFile($file)
    {

    }

    public static function compileString($html, $context)
    {
        $html = <<<PHPX
            <?phpx 
            <> 
                <TestComponent attr1="1" />
                <TestComponent attr1={objs} attr2="{abc}">
                    aaaaa
                </TestComponent>
            <>
            123
        PHPX;

        $phpxDocToken = TokenSelector::findNext($html, "<?phpx", null);

        if($phpxDocToken->isValid()){
            $phpxDoc = $phpxDocToken->getString();
        }

        $offset = 0;

        while (($selection = TokenSelector::findNext($phpxDoc, "<", ">", $offset))->isValid()) {

            $elementString = $selection->getString();
            $hasAutoclosure = (substr($elementString, -1) == "/");

            $outChildren = '';
            if($hasAutoclosure){
                $outChildren = 'null';
            }else{
                $outChildren = '';

                ChildrenSelector::findNext($phpxDoc, '')
            }

           /* "new Element('Empthy', null)"
            "new Element('Empthy', new )"*/

            if($elementString == '')
            {
                return static::compileSelection($phpxDoc, $selection, "new Element('Empthy')");
            }

            $offset++;

        }

        return $phpxDoc;
    }

    
    /**
     * Compile a selection into the html string. Selection can then replace only a part of the string with another one.
     * 
     * @param string $html The selection to compile.
     * @param Selector $selection The selection to compile.
     * @param string $replaceString The selection to compile.
     * 
     * @return string The new HTML content string with the applied replacement.
     */
    public static function compileSelection(string $html, Selector $selection, string $replaceString): string
    {
        $preSelectionString = substr($html, 0, $selection->getStartPosition());
        $postSelectionString = substr($html, $selection->getEndPosition());
        return $preSelectionString . $replaceString . $postSelectionString;
    }
}
