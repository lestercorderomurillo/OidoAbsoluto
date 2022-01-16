<?php

namespace Cosmic\Utilities;

use Cosmic\DOM\Element;

/**
 * This helper class is used to provide methods for HTML manipulation.
 */
class HTML
{

    /**
     * Encode the string value inside a javascript tag.
     * 
     * @param string $value The script value to encapsulate.
     * @param bool $addBodyReady If true, this method will append a jquery onDocumentLoad callback to the script.
     * 
     * @return string The compiled string.
     */
    public static function encapsulateInElement(string $children, Element $element, bool $requireReady = true)
    {
        if (strlen($value) > 0) {

            if($addBodyReady){
                $value = <<<JS
                $(function() {
                $value
                });
                JS;
            }

            $value = <<<HTML
            <script type="text/javascript">
            $value
            </script>
            HTML;

        }
        return $value;
    }

    /**
     * Encode the string value inside a javascript tag.
     * 
     * @param string $value The script value to encapsulate.
     * @param bool $addBodyReady If true, this method will append a jquery onDocumentLoad callback to the script.
     * 
     * @return string The compiled string.
     */
    public static function encodeInJScript(string $value, bool $addBodyReady = true)
    {
        if (strlen($value) > 0) {

            if($addBodyReady){
                $value = <<<JS
                $(function() {
                $value
                });
                JS;
            }

            $value = <<<HTML
            <script type="text/javascript">
            $value
            </script>
            HTML;

        }
        return $value;
    }

    /**
     * Select the body of a cosmic element.
     * 
     * @param string $html The html value to encode.
     * @param string $componentName The name of the component to look for.
     * @param int $initialOffset The position to start looking for the closing tag.
     * 
     * @return Selection|null The selection to compile. Return null if not body has been found.
     */
    public static function findElementBody(string $html, string $componentName, int $initialOffset)
    {
        $level = 0;
        $offset = $initialOffset;

        while (($htmlStrip = Pattern::select($html, "<", ">", $offset))->isValid()) {

            $isCloseTag = false;

            if ($html[$htmlStrip->getStartPosition() + 1] == "/") {
                $isCloseTag = true;
                $htmlStrip->moveStartPosition();
            }

            if ((explode(" ", $htmlStrip->getString(true), 2)[0]) == $componentName) {

                if ($isCloseTag) {

                    if ($level == 0) {
                        
                        return new Selection($html, $initialOffset, $htmlStrip->getEndPosition() - strlen("</$componentName>"), "<", ">");

                    } else {
                        $level--;
                    }

                } else {

                    $level++;

                }

            }

            $offset = $htmlStrip->getEndPosition();
        }

        return null;
    }
}