<?php

namespace Cosmic\Utilities;

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
}