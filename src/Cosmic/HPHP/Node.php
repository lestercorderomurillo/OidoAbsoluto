<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\HPHP;

use Cosmic\Utilities\Strings;
use Cosmic\Traits\StringableTrait;
use Cosmic\Utilities\Collections;
use Cosmic\HPHP\Interfaces\RenderableInterface;
use Cosmic\HPHP\Providers\CSSPropertyNamesProvider;

/**
 * This class represents a cosmic virtual node. Similar to an HTML object.
 */
class Node implements RenderableInterface
{
    use StringableTrait;

    /**
     * List of tags names that should be child-less.
     */
    const singleLineNodeTagNames = [
        'br', 'input', 'link', 'meta', '!doctype', 'basefont', 'base', 'area',
        'hr', 'wbr', 'param', 'img', 'isindex', '?xml', 'embed', '?php', '?', '?='
    ];

    private $depth;

    /**
     * @var string $tagName The tag name to use as the template.
     */
    private string $tagName;

    /**
     * @var bool $isNativeType Indicates if this component is a generic HTML virtual node.
     */
    private bool $isNativeType;

    /**
     * @var array $props The parameters that will be used to render the virtual node. 
     */
    private array $props;

    /**
     * @var string[] $cssProps The css props that will be used to render the virtual node. 
     */
    private array $cssProps;

    /**
     * @var Node[]|null $children The children nodes of the virtual node.
     */
    private $children;

    /**
     * @var mixed $parent The parent node or context of the virtual node.
     */
    private $parent;

    /**
     * @var string[] $events The delegated events for this virtual node.
     */
    private array $events;

    /**
     * Constructor.
     * 
     * @param mixed $tagName
     * @param array $props
     * @param Node[]|string|null $children
     * @param mixed $parent
     * @return void
     */
    public function __construct(string $tagName, array $props = [], $children = null, $parent = null)
    {
        $this->depth = 0;
        $this->tagName = $tagName;
        $this->isNativeType = preg_match('/^\p{Lu}/u', $this->tagName) ? false : true;
        $this->children = $children;
        $this->parent = $parent;

        $this->cssProps = [];
        $this->events = [];

        foreach ($props as $key => $value) {

            if (str_starts_with($key, "on")) {
                $this->events[$key] = $value;
            } else if (Strings::containsOne(Strings::camelToDashed($key), CSSPropertyNamesProvider::provide())) {
                $this->cssProps[Strings::camelToDashed($key)] = $value;
            } else {
                $this->props[$key] = $value;
            }
        }

        if (!isset($this->props["id"])) {
            $this->props["id"] = generateID();
        }
    }

    /**
     * Return the tag name associated with this virtual node.
     * 
     * @return string The tag name.
     */
    public function getTagName(): string
    {
        return $this->tagName;
    }

    /**
     * Return the parent context from this virtual node.
     * 
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Return the children nodes of this virtual node.
     * 
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Return the component name associated with this virtual node.
     * 
     * @return bool The component name.
     */
    public function isNativeType(): bool
    {
        return $this->isNativeType;
    }

    public function isHTMLSingleNode(): bool
    {
        return Strings::containsOne($this->tagName, static::singleLineNodeTagNames);
    }

    /**
     * Return the stored properties for this virtual node.
     * 
     * @return array The properties array.
     */
    public function getProps()
    {
        return $this->props;
    }

    /**
     * Return the stored events for this virtual node.
     * 
     * @return array The properties array.
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Return the stored events for this virtual node.
     * 
     * @return array The properties array.
     */
    public function getCSSProps(): array
    {
        return $this->cssProps;
    }

    public function getIndentation()
    {
        return str_repeat("\t", $this->depth);
    }

    public function render(): string
    {
        $indent = $this->getIndentation();

        if ($this->isHTMLSingleNode()) {

            return "$indent<$this->tagName>\n";
        } else {

            return "$indent<$this->tagName>\n" . $this->renderChildren() . "$indent</$this->tagName>\n";
        }
    }

    public function renderChildren(): string
    {
        $out = [];

        if (is_array($this->children)) {

            foreach ($this->children as $node) {
                if ($node instanceof Node) {
                    $node->depth = $this->depth + 1;
                    $out[] = $node->render();
                }
            }
        }

        return implode('', $out);


        /*if (is_string($this->children)) {
            return "\n" . str_repeat("\t", $this->depth) . trim($this->children) . "\n";
        }*/

        return '';
    }

    public function toString()
    {
        return $this->render();
    }
}
