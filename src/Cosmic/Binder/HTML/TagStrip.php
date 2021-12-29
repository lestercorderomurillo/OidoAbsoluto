<?php

namespace Cosmic\Binder\HTML;

use Cosmic\Binder\Compiler;
use Cosmic\Traits\StringableTrait;
use function Cosmic\Core\Bootstrap\app;

/**
 * This class represents a simple HTML tag strip. Not used to manage cosmic elements but already parsed HTML ones.
 */
class TagStrip
{
    use StringableTrait;

    const cannotBeEmptyTags = ["id", "name", "for"];

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
        $attributesString = "";

        $this->attributes = app()->get(Compiler::class)->compileMultivalueAttributes($this->attributes);

        foreach ($this->attributes as $key => $value) {

            if (is_int($key)) {

                $attributesString .= " $value";
                
            } else {

                if ($key == "html") {
                    $attributesString .= " $key";
                } else if (in_array($key, self::cannotBeEmptyTags)) {
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
