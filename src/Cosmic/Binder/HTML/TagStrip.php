<?php

namespace Cosmic\Binder\HTML;

use Cosmic\Binder\Compiler;
use Cosmic\Binder\DOM;
use Cosmic\Traits\StringableTrait;
use Cosmic\Utilities\Text;

/**
 * This class represents a simple HTML tag strip. Not used to manage cosmic elements but already parsed HTML ones.
 */
class TagStrip
{
    use StringableTrait;

    /**
     * @var array $attributes The HTML tag.
     */
    private string $tag;

    /**
     * @var array $attributes The internal storage of attributes.
     */
    private array $attributes;

    /**
     * Constructor. Creates a new HTML Tag Strip.
     * 
     * @param string $tag The output tag to render.
     * @param array $attributes An array of attributes.
     * 
     * @return void
     */
    public function __construct(string $tag, array $attributes = [])
    {
        $this->tag = strtolower($tag);
        $this->attributes = $attributes;
    }

    /**
     * Render the current TagString into a compiled HTML string.
     * 
     * @return string
     */
    public function toString(): string
    {
        $attributesString = __EMPTY__;

        $this->attributes = app()->get(Compiler::class)->compileMultivalueAttributes($this->attributes);

        foreach ($this->attributes as $key => $value) {

            if (is_int($key)) {

                $attributesString .= " $value";
            } else {

                if (str_starts_with($key, "(") && str_ends_with($key, ")")) {

                    $event = substr($key, 1, -1);

                    $id = safe($this->attributes["id"]);

                    if ($id == null) {
                        throw new \RuntimeException("Event handlers require an id attribute to identify the DOM element");
                    }

                    $value = trim($value);

                    if ($event == "update") {

                        $value = strtr($value, ["()" => ""]);

                        $handleCode = <<<JS
                        $(window).on("load", function() {
                            setInterval($value, 16);
                        });
                        JS;
                    } else {

                        if ($event == "load") {
                            $id = "window";
                        } else {

                            $id = '"#' . strtr($id, ["#" => "\\\#"]) . '"';
                        }

                        if (!str_ends_with($value, ";")) {
                            $value .= ";";
                        }

                        $handleCode = <<<JS
                        $($id).on("$event", function() {
                            $value
                        });
                        JS;
                    }

                    app()->get(DOM::class)->registerJavascriptSourceCode($handleCode);
                } else if (Text::contains($key, ["id", "name", "for", "key"])) {

                    if (strlen($value) > 0) {
                        $attributesString .= " $key=\"$value\"";
                    }
                } else {
                    $attributesString .= " $key=\"$value\"";
                }
            }
        }

        return "<" . trim($this->tag . $attributesString) . ">";
    }
}
