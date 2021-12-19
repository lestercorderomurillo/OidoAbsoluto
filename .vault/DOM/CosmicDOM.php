<?php

namespace Cosmic\DOM;

use Cosmic\Core\Types\View;
use Cosmic\DOM\HTML\BodyBeautifier;
use Cosmic\Utilities\Collection;

use function Cosmic\Kernel\app;
use function Cosmic\Kernel\safe;

class CosmicDOM
{
    private BodyBeautifier $body_beatifier;
    private array $components;
    private array $callbacks;
    private array $state;
    private array $tokens;

    public function __construct()
    {
        $this->clearAll();
    }

    public function clearAll(): void
    {
        $this->body_beatifier = new BodyBeautifier();
        $this->components = [];
        $this->callbacks = [];
        $this->states = [];
        $this->tokens = [];
    }

    public function getComponent(string $component_name): Component
    {
        return $this->components[$component_name];
    }

    public function getBufferedComponentScripts(): string
    {
        return "";
    }

    public function getPublicTokens(): array
    {
        return $this->tokens;
    }

    public function isComponentRegistered(string $component_name)
    {
    }

    public function registerAllComponentsFromView(View $view): void
    {
    }

    public function registerComponent(Component $component)
    {
        $this->components[$component->getName()] = $component;  
    }

    public function registerState(string $script)
    { 
    }

    public function registerCallback(string $callback)
    {
        
    }

    public function registerPublicToken(string $token, $value)
    {
        $this->tokens[$token] = $value;
    }
    


    public function renderView(View $view)
    {

        /*$html = $view->getSourceHTML();

        $view_tokens = Collection::mapKeys($view->getViewData(), function($key){
            return "view->" . $key;
        });

        $this->renderViewRecursive($html, )

        $html = (new BodyBeautifier())->beautifyString($output);

        $replacers = [
            "view->callbacks" => "",
            "view->states" => "",
        ];  */
    }

    public function renderElement(CosmicElement $element, array $attributes, array $inherit_tokens)
    {
    }
    
    /**
     * Creates a new cosmic element with the given raw input.
     * Ex: $input = "<Component></component>"
     *
     * @return array
     */
    public static function createElement(string $input): array
    {
        /*$input = preg_replace('!\s+!', ' ', $input);
        //$input = preg_replace("(\s{0,4})=(\s{0,4})\"", '="', $input);
        $splitted = explode(" ", $input, 2);

        $tag = $splitted[0];
        $attributes = [];

        if (isset($splitted[1])) {
            $attributes_split = Text::quotedExplode($splitted[1]);
            foreach ($attributes_split as $attribute) {
                $key_value_split = Text::multiExplode(["='", "=\""], $attribute);
                $key = $key_value_split[0];
                if (isset($key_value_split[1])) {
                    $value = $key_value_split[1];
                } else {
                    $value = null;
                }
                if (isset($value)) {
                    if ($value[strlen($value) - 1] == "\"" || $value[strlen($value) - 1] == "'") {
                        $value = substr($value, 0, -1);
                    } else {
                        throw new CompileException("Cannot parse key value from string object.");
                    }
                }
                $attributes["$key"] = $value;
            }
        }
        $el = app()->create(CosmicElement::class);
        $el->*/
    }

}
