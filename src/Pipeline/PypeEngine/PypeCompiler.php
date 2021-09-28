<?php

namespace Pipeline\PypeEngine;

use Exception;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\FilePath;
use Pipeline\FileSystem\Path\SystemPath;
use Pipeline\HTTP\Server\ServerResponse;
use Pipeline\PypeEngine\Inproc\HTMLStrip;
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

    public static function renderString(string $html, array $context = []): string
    {
        try {
            // As recursion goes, this will update i, its.. etc, and body will be replaced accordingly
            $output = ArrayHelper::parameterReplace($html, $context);
            $offset = 0;

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

                                $body_selection = BodyFinder::detectBody($output, "app:" . $domtag, $selection->getEndPosition() + 1);

                                $component->setBody($body_selection->getReducedString());
                                $selection->setEndPosition($body_selection->getEndPosition() + strlen("</app:$domtag>"));
                            }

                            // Render this component
                            $result = $component->render() . "\n";

                            // Write the output after has been rendered.
                            $output = self::writeOnSelection($selection, $output, $result);

                            break;

                        case "this":

                            $value = ArrayHelper::parameterReplace($context["this.class"], $context);
                            if ($value != null) {

                                if (!StringHelper::startsWith($value, "app-")) {
                                    $value = "app-$value";
                                }

                                $context["this.class"] = $value;
                                /*$path = new FilePath(SystemPath::WEB, "build", "css");
                                $css_data = FileSystem::includeAsString($path, false);
                                $class = "." . $context->get("componentClass");
                                if (!StringHelper::contains($css_data, $class)) {
                                    //$context->remove("componentClass");
                                }*/
                            }

                            $template = new PypeTemplate($context["this"]);

                            $attributes = [];

                            if (!$closure) {

                                $attributes = self::componentStringToArray($selection->getReducedString())[1];
                                $attributes["class"] = trim($context["this.class"] . " " .
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

                                    $body_selection = BodyFinder::detectBody($output, "ifdef", $selection->getEndPosition() + 1);

                                    // Write the output after has been rendered.
                                    $selection->moveStartPosition(-1);
                                    $selection->setEndPosition($body_selection->getEndPosition() + strlen("</ifdef>"));

                                    $ifdef_string = "";
                                    if (isset($context[$check])) {
                                        $ifdef_string = ltrim(self::renderString($body_selection->getReducedString(), $context));
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
                                    if (!isset($context["$foreach_from_name"])) {
                                        var_dump($context);
                                        throw new CompileException("Foreach failure: $foreach_from_name array is missing from context.");
                                    }

                                    // Find the body of this for this foreach loop. Ex: <foreach>...body...</foreach>
                                    /* $body_finder = new BodyFinder($selection, $output, "foreach");
                                    $body_selection = $body_finder->getSelection();*/

                                    $body_selection = BodyFinder::detectBody($output, "foreach", $selection->getEndPosition() + 1);

                                    $foreach_string = "";

                                    // Execute the foreach loop and render the next components in a recursive fashion
                                    if (ArrayHelper::is2Dimensional($context["$foreach_from_name"])) {

                                        foreach ($context["$foreach_from_name"] as $array => $object) {

                                            // Register the objects tokens for usage
                                            foreach ($object as $_key => $_value) {
                                                $tokens["$item_name.$_key"] = $_value;
                                            }
                                            // Special case for var_export on 2Dimensional objects as they are not normal strings
                                            $tokens["$item_name"] = preg_replace('/\s+/', ' ', trim(var_export($object, true)));
                                            $foreach_string .= ltrim(self::renderString($body_selection->getReducedString(), $tokens, $context));
                                        }
                                    } else {

                                        foreach ($context["$foreach_from_name"] as $array) {

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
        $offset = 0;

        while (($selection = PatternHelper::selectStringByQuotes($output, "{?", "}", $offset, 0))->isValid()) {
            $id = substr($selection->getReducedString(), 2);
            if ($id != null && strlen($id) > 0) {
                $initial = self::staticTryGet($tokens["$id"], "");
                $output = self::writeOnSelection($selection, $output, "<div class=\"app-sync-$id d-inline pr-1\">$initial</div>");
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

}
