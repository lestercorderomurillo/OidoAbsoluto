<?php

namespace Pipeline\PypeEngine;

use Exception;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\FilePath;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\HTTP\Server\ServerResponse;
use Pipeline\PypeEngine\Inproc\HTMLStrip;
use Pipeline\PypeEngine\Inproc\RenderContext;
use Pipeline\PypeEngine\Inproc\Selection;
use Pipeline\PypeEngine\Inproc\BodyFinder;
use Pipeline\PypeEngine\Inproc\HTMLBeautifier;
use Pipeline\PypeEngine\Exceptions\CompileException;
use Pipeline\Security\Cryptography;
use Pipeline\Utilities\ArrayHelper;
use Pipeline\Utilities\PatternHelper;
use Pipeline\Utilities\StringHelper;
use Pipeline\Traits\DefaultAccessorTrait;

class PypeCompiler
{
    use DefaultAccessorTrait;
    const HTML_KEYWORDS = ["ifdef", "app:", "foreach ", "for ", "this"];

    private static $counter_test = 0;

    public static function getDefaultViewData(View &$view)
    {
        $packages = [];
        $scripts = [];
        $styles = [];

        $packages[] = new FilePath(SystemPath::PACKAGES, "jquery-3.6.0/jquery", "min.js");
        $packages[] = new FilePath(SystemPath::PACKAGES, "popper-1.16.1/popper", "min.js");
        $packages[] = new FilePath(SystemPath::PACKAGES, "bootstrap-4.6.0/bootstrap", "min.js");
        $packages[] = new FilePath(SystemPath::PACKAGES, "observable-slim-0.1.5/observable-slim", "min.js");
        $packages[] = new FilePath(SystemPath::PACKAGES, "jquery-validate-1.11.1/jquery.validate", "min.js");

        $styles[] = "https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100&display=swap";
        $styles[] = new FilePath(SystemPath::PACKAGES, "bootstrap-4.6.0/bootstrap", "css");
        $styles[] = new FilePath(SystemPath::PACKAGES, "font-awesome-4.7.0/font-awesome", "css");
        $styles[] = new FilePath(SystemPath::WEB, "build", "css");

        $scripts = ArrayHelper::stackLines($packages, FileSystem::findWebPaths(new DirectoryPath(SystemPath::BUILDIN, "Scripts"), "js"));

        $data = [
            "headers" =>
            [
                [
                    "name" => "timestamp",
                    "content" => $view->getTimestamp()
                ],
                [
                    "name" => "page",
                    "content" => $view->getViewGUID()
                ]
            ],
            "scripts" => FileSystem::toWebPaths($scripts),
            "styles" => FileSystem::toWebPaths($styles),
        ];

        return $data;
    }

    public static function renderString(string $html, array $tokens = [], RenderContext $context = null): string
    {

        try {

            if ($context == null) {
                $context = new RenderContext();
            }

            // As recursion goes, this will update i, its.. etc, and body will be replaced accordingly
            $output = ArrayHelper::parameterReplace($html, $tokens);
            $offset = 0;

            // Replace tokens inside tokens but only on certain
            $tokens = ArrayHelper::parameterReplace($tokens, $tokens);

            while (($selection = PatternHelper::selectStringByQuotes($output, "<", ">", $offset, 0))->isValid()) {

                $closure = false;
                if ($output[$selection->getStartPosition() + 1] == "/") {
                    $closure = true;
                }

                $offset = $selection->getEndPosition() + 1;
                $keyword = null;

                foreach (self::HTML_KEYWORDS as $find_keyword) {
                    if (!is_bool(PatternHelper::findByText($selection->getReducedString(), $find_keyword))) {
                        $keyword = $find_keyword;
                        break;
                    }
                }

                if ($keyword != null) {

                    switch (trim($keyword)) {

                        case "app:":

                            $component_array = self::componentStringToArray($selection->getReducedString());
                            $domtag = substr($component_array[0], 5);
                            $attributes = $component_array[1];

                            $template = new PypeTemplate($domtag);
                            $component = new PypeComponent($template, $attributes, $context);

                            if ($template->requireClosure()) {

                                // Find the body of this for this individual component and its body contents
                                //$body_finder = new BodyFinder($selection, $output, "app:$domtag");

                                $body_selection = BodyFinder::detectBody($output, "app:".$domtag, $selection->getEndPosition() + 1);

                                $component->setBody($body_selection->getReducedString());
                                $selection->setEndPosition($body_selection->getEndPosition() + strlen("</app:$domtag>"));

                            }

                            // Render this component
                            $result = $component->render() . "\n";

                            // Write the output after has been rendered.
                            $output = self::writeOnSelection($selection, $output, $result);

                            break;

                        case "this":

                            $value = ArrayHelper::parameterReplace($context->get("componentClass"), $tokens);
                            if ($value != null) {

                                if (!StringHelper::startsWith($value, "app-")) {
                                    $value = "app-$value";
                                }

                                $context->set("componentClass", $value);
                                $path = new FilePath(SystemPath::WEB, "build", "css");
                                $css_data = FileSystem::includeAsString($path, false);
                                $class = "." . $context->get("componentClass");
                                if (!StringHelper::contains($css_data, $class)) {
                                    $context->remove("componentClass");
                                }
                            }

                            $template = new PypeTemplate($tokens["this"]);

                            $attributes = [];

                            if (!$closure) {

                                $attributes = self::componentStringToArray($selection->getReducedString())[1];
                                $attributes["class"] = trim($context->get("componentClass") . " " .
                                    self::staticTryGet($attributes["class"], ""));

                                self::parseMultivalueFields($attributes);

                                $output = self::writeOnSelection($selection, $output, new HTMLStrip($template->getPrototype(), $attributes));
                            } else {
                                $output = self::writeOnSelection($selection, $output, new HTMLStrip("/" . $template->getPrototype()));
                            }

                            break;

                        case "ifdef":

                            if (!$closure) {
                                $attributes = self::componentStringToArray($selection->getReducedString())[1];
                                $check = self::staticTryGet($attributes["check"]);

                                if ($check != null) {
                                    /*$body_finder = new BodyFinder($selection, $output, "ifdef");
                                    $body_selection = $body_finder->getSelection();*/

                                    $body_selection = BodyFinder::detectBody($output,"ifdef", $selection->getEndPosition() + 1);

                                    // Write the output after has been rendered.
                                    $selection->moveStartPosition(-1);
                                    $selection->setEndPosition($body_selection->getEndPosition() + strlen("</ifdef>"));

                                    $ifdef_string = "";
                                    if ($context->has($check)) {
                                        $ifdef_string = ltrim(self::renderString($body_selection->getReducedString(), $tokens, $context));
                                    }

                                    $result = trim($ifdef_string);
                                    $output = self::writeOnSelection($selection, $output, $result);
                                }
                            }
                            break;

                        case "for":

                            if (!$closure) {

                                $attributes = self::componentStringToArray($selection->getReducedString())[1];
                                $item_name = self::staticTryGet($attributes["name"], "i");

                                if (is_string($item_name) && PatternHelper::isNumber($attributes["start"]) && PatternHelper::isNumber($attributes["end"])) {

                                    // Loop range (number of iterations)
                                    $for_start = (int)$attributes["start"];
                                    $for_end = (int)$attributes["end"];

                                    // Find the body of this for this foreach loop. Ex: <for>...body...</for>
                                    /*$body_finder = new BodyFinder($selection, $output, "for");
                                    $body_selection = $body_finder->getSelection();*/

                                    $body_selection = BodyFinder::detectBody($output, "for", $selection->getEndPosition() + 1);

                                    // Execute the for loop and render the next components in a recursive fashion
                                    $for_string = "";
                                    $step = $for_end - $for_start;

                                    if ($step > 0) {
                                        for ($i = $for_start; $i <= $for_end; $i++) {

                                            $tokens["$item_name"] = $i;
                                            $tokens = ArrayHelper::mergeNamedValues($tokens, ["this.random" => Cryptography::computeRandomKey(8)]);
                                            $for_string .= ltrim(self::renderString($body_selection->getReducedString(), $tokens, $context));
                                        }
                                    } else {
                                        for ($i = $for_start; $i >= $for_end; $i--) {
                                            $tokens["$item_name"] = $i;
                                            $tokens = ArrayHelper::mergeNamedValues($tokens, ["this.random" => Cryptography::computeRandomKey(8)]);
                                            $for_string .= ltrim(self::renderString($body_selection->getReducedString(), $tokens, $context));
                                        }
                                    }

                                    // Write the output after has been rendered.
                                    $selection->moveStartPosition(-1);
                                    $selection->setEndPosition($body_selection->getEndPosition() + strlen("</for>"));

                                    $result = " " . trim($for_string);
                                    $output = self::writeOnSelection($selection, $output, $result);
                                }
                            }

                            break;

                        case "foreach":

                            if (!$closure) {

                                $attributes = self::componentStringToArray($selection->getReducedString())[1];
                                $item_name = self::staticTryGet($attributes["name"]);

                                if (is_string($item_name) && is_string($attributes["from"])) {

                                    // Get foreach attributes
                                    $foreach_from_name = $attributes["from"];
                                    if (!$context->has("$foreach_from_name")) {
                                        throw new CompileException("Foreach failure: $foreach_from_name array is missing from RenderContext/ViewData.");
                                    }

                                    // Find the body of this for this foreach loop. Ex: <foreach>...body...</foreach>
                                    /* $body_finder = new BodyFinder($selection, $output, "foreach");
                                    $body_selection = $body_finder->getSelection();*/

                                    $body_selection = BodyFinder::detectBody($output, "foreach", $selection->getEndPosition() + 1);

                                    $foreach_string = "";

                                    // Execute the foreach loop and render the next components in a recursive fashion
                                    if (ArrayHelper::is2Dimensional($context->get("$foreach_from_name"))) {

                                        foreach ($context->get("$foreach_from_name") as $array => $object) {

                                            // Register the objects tokens for usage
                                            foreach ($object as $_key => $_value) {
                                                $tokens["$item_name.$_key"] = $_value;
                                            }
                                            // Special case for var_export on 2Dimensional objects as they are not normal strings
                                            $tokens["$item_name"] = preg_replace('/\s+/', ' ', trim(var_export($object, true)));
                                            $foreach_string .= ltrim(self::renderString($body_selection->getReducedString(), $tokens, $context));
                                        }
                                    } else {

                                        foreach ($context->get("$foreach_from_name") as $array) {

                                            $tokens["$item_name"] = $array;
                                            $foreach_string .= ltrim(self::renderString($body_selection->getReducedString(), $tokens, $context));
                                        }
                                    }

                                    // Write the output after has been rendered.
                                    $selection->moveStartPosition(-1);
                                    $selection->setEndPosition($body_selection->getEndPosition() + strlen("</foreach>"));

                                    $result = " " . trim($foreach_string);
                                    $output = self::writeOnSelection($selection, $output, $result);
                                }
                            }

                            break;
                    }
                }
            }
        } catch (Exception $e) {
            ServerResponse::create(500, $e->getMessage())->sendAndExit();
        }

        $output = str_replace("</script>", "</script>\n", $output);
        $html_beautifyr = new HTMLBeautifier();
        $output = $html_beautifyr->beautifyString($output);

        while (($selection = PatternHelper::selectStringByQuotes($output, "{?", "}", $offset))->isValid()) {
            $id = substr($selection->getReducedString(), 2, -1);
            if ($id != null && strlen($id) > 0) {
                $initial = self::staticTryGet($tokens["$id"], "");
                self::writeOnSelection($selection, $output, "<div id=\"app-sync-$id\" class=\"d-inline\">$initial</div>");
            }

            $offset = $selection->getEndPosition() + 1;
        }

        return $output;
    }

    private static function writeOnSelection(Selection &$selection, &$source, $replace): string
    {
        $pre = substr($source, 0, $selection->getStartPosition());
        $post = substr($source, $selection->getEndPosition() + 1);
        return $pre . $replace . $post;
    }

    private static function &parseMultivalueFields(array &$attributes): array
    {
        foreach ($attributes as $attribute => $value) {

            $multivalue = explode("&", $attribute);

            if (count($multivalue) > 1) {
                unset($attributes[$attribute]);
                foreach ($multivalue as $multi) {
                    $attributes[$multi] = $value;
                }
            }
        }

        ksort($attributes);
        return $attributes;
    }

    public static function componentStringToArray(string $input): array
    {
        $splitted = explode(" ", $input, 2);
        $tag = $splitted[0];
        $attributes = [];

        if (isset($splitted[1])) {

            $attributes_split = StringHelper::quotedExplode($splitted[1]);

            foreach ($attributes_split as $attribute) {
                $key_value_split = StringHelper::multiExplode(["='", "=\""], $attribute);
                $key = $key_value_split[0];

                if (isset($key_value_split[1])) {
                    $value = $key_value_split[1];
                } else {
                    $value = NULL;
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
        return [$tag, $attributes];
    }













    /*
    public function export(string $value): void
    {
        $this->output .= $value;
    }

    public function getCompiled(): string
    {
        return $this->output;
    }

    public function renderView(): string
    {
        $this->executeRenderingPipeline();
        return $this->getCompiled();
    }

    public function executeRenderingPipeline(): void
    {
        $context = new RenderContext();
        $context->set("html", $this->getView()->getSourceHTML());
        $this->renderDoctype($context);
    }
/*
    public function fetchFragment(): ViewFragment
    {
        return new ViewFragment();
    }*/
    /*
    public function compileFragment(RenderContext $context, ViewFragment $fragment): string
    {
        return "";
    }*/
    /*
    public function renderDoctype(RenderContext $context): RenderContext 
    {
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang='en'>
        <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        HTML;

        $this->export($html);
        return $context;
    }

    public function renderHeaders(RenderContext $context): RenderContext
    {
        $html = <<<HTML
        <!DOCTYPE html>
        <html lang='en'>
        <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        HTML;

        return $context;
    }

    public function renderFooter(RenderContext $context): RenderContext
    {
        return $context;
    }

    public function renderScript(RenderContext $context): RenderContext
    {
        return $context;
    }

    public function renderComponents(RenderContext $context): RenderContext
    {
        return $context;
    }

    public function renderResponsiveElements(RenderContext $context): RenderContext
    {
        return $context;
    }

    public function includeMeta(array $meta_tags): void
    {
        $this->meta_tags = ArrayHelper::mergeNamedValues($this->meta_tags, $meta_tags);
    }

    public function getContextlessView(): View
    {
        return $this->_view;
    }

    public function getView(): View
    {
        return $this->view;
    }

    public function setView(View $view): string
    {
        return "";
    }*/
}
