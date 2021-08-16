<?php

namespace VIP\Component;

use VIP\Component\HTMLObject;
use VIP\Core\RenderableInterface;
use VIP\Factory\ResponseFactory;
use VIP\FileSystem\WebDirectory;
use VIP\Utilities\ArrayHelper;
use VIP\Security\Cryptography;

use function VIP\Core\Services;

class Component extends HTMLObject implements RenderableInterface
{
    private array $system_tokens;
    private int $transverse_depth;

    public function __construct(string $tag, array $attributes = [])
    {
        parent::__construct($tag, $attributes);

        $this->transverse_depth = 0;
        $this->system_tokens = [];

        if (!Template::isRegistered($this->getDOMTag())) {
            (ResponseFactory::createError(500, "Trying to render the component \"$tag\" failed.<br> 
            Unregistered component. Check your view/components files."))->handle();
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
        $this->system_tokens = ArrayHelper::merge($array, $this->system_tokens);
        return $this;
    }

    public function getSystemReplaceTokens(): array
    {
        return $this->system_tokens;
    }

    public function render(): string
    {
        $template = new Template($this->getDOMTag(), $this->getTransverseDepth());

        $html_output = "";
        $html_defaults = [];
        $html_view_tokens = [];
        $html_system_tokens = [];
        $html_final_tokens = [];

        $object_separator = "\r\n";

        // Load component defaults values from templates
        for ($depth = $this->getTransverseDepth(); $depth >= 0; $depth--) {
            $html_defaults = ArrayHelper::merge($html_defaults, (new Template($this->getDOMTag(), $depth))->get("defaults", []));
        }

        // Load components values from view
        $html_view_tokens = $this->getAttributes();

        // Create system replace tokens first
        // Load system replace tokens we just created
        if ($this->getTransverseDepth() == 0) {
            $this->addSystemReplaceToken([
                "@random-unique" => Cryptography::computeRandomKey(8),
                "@content" => (new WebDirectory(__WEB_NAME__ . "/"))->toString(),
                "@url" => __URL__,
                "@counter" => Services("ViewRenderer")->getCurrentCounter()
            ]);
        }

        $this->addSystemReplaceToken([
            "@random" => Cryptography::computeRandomKey(8)
        ]);

        $html_system_tokens = $this->getSystemReplaceTokens();

        // Join all replace values
        $html_final_tokens = ArrayHelper::merge($html_final_tokens, $html_system_tokens);
        $html_final_tokens = ArrayHelper::merge($html_final_tokens, $html_view_tokens);
        $html_final_tokens = ArrayHelper::merge($html_final_tokens, $html_defaults);

        // Now start the rendering and replace tokens when needed
        $prototype = ArrayHelper::parameterReplace($template->get("prototype", ""), $html_final_tokens);
        $function = $template->get("function", NULL);

        if ($function != NULL && $template->has("prototype")) {
            (ResponseFactory::createError(500, "Component \"" . $this->getDOMTag() . "\"
            cannot be a function and a prototype at the same time. Check component files."))->handle();
        }

        if (!$this->isClosure()) {

            foreach ($template->get("required", []) as $required) {
                if (!$this->hasAttribute($required)) {
                    (ResponseFactory::createError(500, "Component \"" . $this->getDOMTag() . "\"
                    is missing a required parameter: $required. Check view files."))->handle();
                }
            }

            if ($function != NULL) {
                $function_parameters = ArrayHelper::parameterReplace($template->get("function-parameters", ""), $html_final_tokens);
                $function_html = $function(Services("ViewRenderer")->getViewData(), $function_parameters);
                if ($function_html === NULL) {
                    (ResponseFactory::createError(500, "Component \"" . $this->getDOMTag() . "\"
                    returned NULL on his function call. Check view files."))->handle();
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

            if ($template->has("concat")) {
                $html_output .= $object_separator .
                    (new Component(
                        $this->getDOMTag(),
                        $this->getAttributes()
                    ))->setTransverseDepth($this->getTransverseDepth() + 1)
                    ->addSystemReplaceToken($this->getSystemReplaceTokens())
                    ->render();
            }
        }

        if ($template->has("closure") && $this->isClosure() && Services("ViewRenderer")->isRenderingClosures()) {
            (ResponseFactory::createError(500, "Component \"" . $this->getDOMTag() . "\"
            don't need manual closure tags. Check view files."))->handle();
        }

        if ($template->has("closure") || $this->isClosure() && $function == NULL) {
            $after = ArrayHelper::parameterReplace($template->get("after", ""), $html_final_tokens);

            if ($template->has("concat")) {
                $html_output .= $object_separator .
                    (new Component(
                        "/" . $this->getDOMTag(),
                        $this->getAttributes()
                    ))->setTransverseDepth($this->getTransverseDepth() + 1)
                    ->addSystemReplaceToken($this->getSystemReplaceTokens())
                    ->render();
            }

            $html_output .= $object_separator . (new HTMLObject("/" . $prototype))->render() . "$after";
        }

        if (!$this->isClosure() && $template->has("new-line") || ($template->has("new-line-after"))) {
            $imax = 1;
            if ($template->get("new-line") != NULL) {
                $imax = $template->get("new-line");
            }
            for ($i = 0; $i < $imax; $i++) {
                $html_output .= $object_separator . (new HTMLObject("br"))->render();
            }
        }

        return trim($html_output);
    }
}
