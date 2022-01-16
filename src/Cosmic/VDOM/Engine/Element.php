<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\VDOM;

use Cosmic\Utilities\Strings;

/**
 * This class represents a cosmic element. Similar to an HTML object.
 */
class Element implements RenderableInterface
{
    /**
     * @var string $tagName The tagName to use as the template.
     */
    private string $tagName;

    /**
     * @var bool $isGenericElement Indicates if this component is a generic HTML element.
     */
    private bool $isGenericElement;

    /**
     * @var string[] $props The parameters that will be used to render the element. 
     * They should be inmutable once created to avoid conflicts later on when doing stateful rendering.
     */
    private array $props;

    /**
     * @var string[] $events The delegated events for this element.
     */
    private array $events;

    /*new Element(
        "div", 
        "<Component /><Component2 />", 
        ["test1" => true, "test2" => true],
        "text-left",
    )*/
    /**
     * Constructor. Creates a new element using the given component as the template.
     * Once the parameters has been set, they cannot be changed anymore, but body content can.
     * 
     * @param string $tagName The component to use as the template.
     * @param string[] $parameters The parameters to use to create this element.
     */
    public function __construct(string $tagName, string $children = "", array $props = [], string $class = "")
    {
        $this->tagName = $tagName;
        $this->children = $children;

        $this->props = $props;
        $this->class = $class;

        $this->cssProps = [];
        $this->events = [];

        foreach ($props as $key => $value){

           /* if (str_starts_with($key, "on")){
                $this->events[$key] = $value;
            }else{
                $this->props[$key] = $value;
            }

            $cssProps = [];

            foreach ($cssProps as $prop){
                if($prop === Strings::camelToDashed($key)){
                    // its css prop!

                }
            }*/

            


        }

        if (!isset($this->parameters["id"])) {
            $this->parameters["id"] = generateID();
        }

        $this->isGenericElement = preg_match('/^\p{Lu}/u', $this->tagName) ? false : true;
    }

    /**
     * Assigns the key with the given value.
     * 
     * @param string $key The key to use.
     * @param string $value The value to assign.
     */
    public function setParameter(string $key, string $value): void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Return the stored property for this element.
     * 
     * @param string $key The entry key.
     * @return string The entry value.
     */
    public function getParameter(string $key): string
    {
        return $this->parameters[$key];
    }

    /**
     * Store an event in the given key.
     * 
     * @param string $key The key to store in.
     * @param string $value The value to store.
     * @return void
     */
    public function setEvent(string $key, string $value): void
    {
        $this->events[$key] = $value;
    }

    /**
     * Return the stored event for this element.
     * 
     * @param string $key The key to retrieve from.
     * @return string The stored event value.
     */
    public function getEvent(string $key): string
    {
        return $this->events[$key];
    }

    /**
     * Return the tag name associated with this element.
     * 
     * @return string The tag name.
     */
    public function getTagName(): string
    {
        return $this->tagName;
    }

    /**
     * Return the component name associated with this element.
     * 
     * @return bool The component name.
     */
    public function isGenericElement(): bool
    {
        return $this->isGenericElement;
    }

    /**
     * Return the stored properties for this element.
     * 
     * @return array The properties array.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Return the stored events for this element.
     * 
     * @return array The properties array.
     */
    public function getEvents(): array
    {
        return $this->events;
    }

}
