<?php

namespace Cosmic\HPHP;

use Cosmic\FileSystem\FS;
use Cosmic\Utilities\Strings;
use Cosmic\HPHP\Engine\Selection;
use Cosmic\HPHP\Engine\Selectors\ChildrenSelector;
use Cosmic\HPHP\Engine\Selectors\TokenSelector;
use Cosmic\HPHP\Interfaces\SelectorInterface;

class Compiler
{

    public static function compileHPHPFile($file)
    {
        $hphpFileContents = FS::read($file);
        return static::compileHPHPSource($hphpFileContents, []);
    }

    private static function compileHPHPSource($hphp, $context)
    {
        $hphpToken = TokenSelector::findNext($hphp, "<?hphp", null);
        $phpOuput = $hphpToken;
        
        $offset = 0;

        while (($functionToken = TokenSelector::findNext($hphpToken, "{", "}", $offset))->isValid()) 
        {
            $outScope = static::compile($functionToken, $context);
            $phpOuput = static::replace($phpOuput, $functionToken, $outScope);
            $offset = $functionToken->getLastPosition();
        }

        return $phpOuput;

    }

    private static function compile(string $hphpString, $context)
    {
        $offset = 0;

        while (($nodeSelection = TokenSelector::findNext($hphpString, "<", ">", $offset))->isValid()) {

            $fp = &$nodeSelection->getFirstPositionHandler();
            $lp = &$nodeSelection->getLastPositionHandler();

            $nodeString = trim($nodeSelection->toString());
            $nodeHasChildren = (substr($nodeString, -1) != '/');
            $nodeString = (!$nodeHasChildren) ? substr($nodeString, 0, -1) : $nodeString;
            $nodeAttSplit = explode(' ', $nodeString, 2);
            $tagName = ($nodeAttSplit[0] == '') ? '' : trim($nodeAttSplit[0]);

            $props = [];
            $propsOut = [];
            $propsString = isset($out[1]) ? trim($out[1]) : '';

            if ($propsString != '') {

                $propsLines = Strings::quotedExplode($propsString, [" "], ["\"", "'"]);

                foreach ($propsLines as $line) {

                    $propSplit = Strings::multiExplode(["=\"", "='", "={"], $line);
                    $propName = $propSplit[0];
                    $propValue = substr($propSplit[1], 0, -1);
                    $props[] = [$propName, $propValue];
                }

                foreach ($props as $arr) {
                    $propsOut[] = "'$arr[0]' => '$arr[1]'";
                }
            }

            $propsOut = implode(", ", $propsOut);

            $fp--;
            $lp++;

            $writeSelection = null;
            
            $childrenSelection = ChildrenSelector::findNext($hphpString, $tagName, $nodeSelection->getLastPosition());

            if ($childrenSelection != null) {

                $childrenContent = trim($childrenSelection->toString());

                $cfp = &$childrenSelection->getFirstPositionHandler();
                $clp = &$childrenSelection->getLastPositionHandler();
                $cfp = $fp;
                $clp = $childrenSelection->getLastPosition() + strlen("</$tagName>");// + 1;

                $isRenderable = Strings::contains($childrenContent, ["<", ">"]);

                if (!$isRenderable) {
                    $childrenOut = "\"" . trim($childrenContent) . "\"";
                } else {
                    $childrenOut = trim($childrenContent);
                }

                $writeSelection = $childrenSelection;

            } else {

                $childrenOut = '';

                $writeSelection = $nodeSelection;
            }

            $compiledValue = "new Node('$tagName', [$propsOut], [$childrenOut])";
            $hphpString = static::replace($hphpString, $writeSelection, $compiledValue);

            $offset = $writeSelection->getLastPosition();
        }

        return $hphpString;
    }


    /**
     * Compile a selection into the source string. 
     * Selection can then replace only a part of the string with another one.
     * 
     * @param string $source The selection to compile.
     * @param Selector $selection The selection to compile.
     * @param string $replaceString The selection to compile.
     * @return string The new HTML content string with the applied replacement.
     */
    public static function replace(string $source, Selection $selection, string $replaceString): string
    {
        $preSelectionString = substr($source, 0, $selection->getFirstPosition());
        $postSelectionString = substr($source, $selection->getLastPosition());
        return $preSelectionString . $replaceString . $postSelectionString;
    }
}
