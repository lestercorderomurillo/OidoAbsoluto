<?php

namespace Pipeline\Pype\Component;

use Pipeline\Pype\ViewRenderer;
use Pipeline\Utilities\ArrayHelper;
use Pipeline\Security\Cryptography;
use Pipeline\HTTP\Server\ServerResponse;
use Pipeline\Pype\Template\ComponentDefinition;
use Pipeline\FileSystem\Path\Web\WebDirectoryPath;

use function Pipeline\Accessors\Dependency;

class Component extends HTMLObject implements DOMInterface
{
    private array $system_tokens;
    private int $transverse_depth;

    public static function create(string $string_input, array $array_input = []): Component
    {
        $placeholder = HTMLObject::create($string_input, $array_input);
        $component = new Component($placeholder->getDOMTag(), $placeholder->getAttributes());
        $component->setClosure($placeholder->isClosure());
        return $component;
    }

    public function __construct(string $tag, array $attributes = [])
    {
        parent::__construct($tag, $attributes);

        $this->transverse_depth = 0;
        $this->system_tokens = [];

        if (!ComponentDefinition::isRegistered($this->getDOMTag())) {
            ServerResponse::create(500, "Trying to render the component \"$tag\" failed. <br>
            Unregistered component. Check your view/components files.")->sendAndExit();
        }
    }

    public function setTransverseDepth(int $depth): Component
    {
        $this->transverse_depth = $depth;
        return $this;
    }

    public function getTransverseDepth(): int
    {
        return $this->transverse_depth;
    }

    public function addSystemReplaceToken(array $array): Component
    {
        $this->system_tokens = ArrayHelper::mergeNamedValues($this->system_tokens, $array);
        return $this;
    }

    public function getSystemReplaceTokens(): array
    {
        return $this->system_tokens;
    }

    public function render(): string
    {
        $view_renderer = Dependency(ViewRenderer::class);
        $template = new ComponentDefinition($this->getDOMTag(), $this->getTransverseDepth());

        $html_output = "";
        $html_defaults = [];
        $html_view_tokens = [];
        $html_system_tokens = [];
        $html_final_tokens = [];

        // Load component defaults values from templates
        for ($depth = $this->getTransverseDepth(); $depth >= 0; $depth--) {
            $html_defaults = ArrayHelper::mergeNamedValues((new ComponentDefinition($this->getDOMTag(), $depth))->get("defaults", []), $html_defaults);
        }

        // Load components values from view
        $html_view_tokens = $this->getAttributes();

        // Create system replace tokens first
        // Load system replace tokens we just created
        if ($this->getTransverseDepth() == 0) {
            $this->addSystemReplaceToken([
                "@random-unique" => Cryptography::computeRandomKey(8),
                "@content" => (new WebDirectoryPath(__WEB_NAME__ . "/"))->toString(),
                "@counter" => $view_renderer->getCurrentCounter(),
                "@url" => __URL__
            ]);
        }

        $this->addSystemReplaceToken([
            "@random" => Cryptography::computeRandomKey(8)
        ]);

        $html_system_tokens = $this->getSystemReplaceTokens();

        // Join all replace values
        $html_final_tokens = ArrayHelper::mergeNamedValues($html_system_tokens, $html_defaults, $html_view_tokens);

        // Now start the rendering and replace tokens when needed
        $prototype = ArrayHelper::parameterReplace($template->get("prototype", ""), $html_final_tokens);
        $function = $template->get("function", NULL);

        if ($function != NULL && $template->has("prototype")) {
            ServerResponse::create(500, "Component \"" . $this->getDOMTag() . "\"
            cannot be a function and a prototype at the same time. Check component files.")->send();
        }

        if (!$this->isClosure()) {

            foreach ($template->get("required", []) as $required) {
                if (!$this->hasAttribute($required)) {
                    ServerResponse::create(500, "Component \"" . $this->getDOMTag() . "\"
                    is missing a required parameter: $required. Check view files.")->send();
                }
            }

            if ($function != NULL) {

                $function_parameters = ArrayHelper::parameterReplace($template->get("function-parameters", ""), $html_final_tokens);
                $function_html = $function($view_renderer->getView()->getViewData(), $function_parameters);
                
                if ($function_html === NULL) {
                    ServerResponse::create(500, "Component \"" . $this->getDOMTag() . "\"
                    returned NULL on his function call. Check view files.")->send();
                }
                
                return trim($function_html);

            } else {

                $before = ArrayHelper::parameterReplace($template->get("before", ""), $html_final_tokens);

                $simple_attributes = ["id", "name", "class", "style", "type", "include"];
                $output_attributes = [];

                foreach ($simple_attributes as $filter) {
                    if ($template->has($filter)) {
                        $data = $template->get($filter);
                        if (is_string($data)) {
                            $output_attributes[$filter] = $data;
                        } else if (is_array($data)) {
                            foreach ($data as $key => $value) {
                                $output_attributes[$key] = $value;
                            }
                        }
                    }
                }

                $content = ArrayHelper::parameterReplace($template->get("content", ""), $html_final_tokens);
                $output_attributes =  ArrayHelper::parameterReplace($output_attributes, $html_final_tokens);

                if (isset($output_attributes["id"]) && in_array($output_attributes["id"], ["", "undefined", "[id]"])) {
                    unset($output_attributes["id"]);
                }

                $html_output = "$before" . (new HTMLObject($prototype, $output_attributes))->render() . "$content";
            }

            if ($template->has("concatElement")) {
                $clone = new Component($this->getDOMTag(), $this->getAttributes());
                $clone->setTransverseDepth($this->getTransverseDepth() + 1);
                $clone->addSystemReplaceToken($this->getSystemReplaceTokens());
                $html_output .= "\n" . $clone->render();
            }
        }

        if ($template->has("closure") && $this->isClosure() &&  $view_renderer->isRenderingClosures()) {
            ServerResponse::create(500, "Component \"" . $this->getDOMTag() . "\"
            don't need manual closure tags. Check view files.")->send();
        }

        if ($template->has("closure") || $this->isClosure() && $function == NULL) {
            $after = ArrayHelper::parameterReplace($template->get("after", ""), $html_final_tokens);

            if ($template->has("concatElement")) {
                $clone = new Component("/" . $this->getDOMTag(), $this->getAttributes());
                $clone->setTransverseDepth($this->getTransverseDepth() + 1);
                $clone->addSystemReplaceToken($this->getSystemReplaceTokens());
                $html_output .= "\n" . $clone->render();
            }

            $html_output .= "\n" . (new HTMLObject("/" . $prototype))->render() . "$after";
        }

        if (!$this->isClosure() && $template->has("new-line") || ($template->has("new-line-after"))) {
            $imax = 1;
            if ($template->get("new-line") != NULL) {
                $imax = $template->get("new-line");
            }
            for ($i = 0; $i < $imax; $i++) {
                $html_output .= "\n" . (new HTMLObject("br"))->render();
            }
        }

        return trim($html_output);
    }
}
