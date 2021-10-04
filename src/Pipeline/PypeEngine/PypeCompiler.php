<?php

namespace Pipeline\PypeEngine;

use Exception;
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
    const HTML_KEYWORDS = ["ifdef", "equal", "notequal", "app:", "foreach ", "for ", "this"];

    public static function createDynamicContext(array &$context, string $base)
    {

        if (ArrayHelper::is2Dimensional($context)) {
            foreach ($context as $key => $value) {
                $context["$base:$key"] = $value;
                self::createDynamicContext($value, "$base:$key");
            }
        }
    }

    public static function renderString(string $html, array $context = []): string
    {
        try {

            $output = ArrayHelper::parameterReplace($html, $context);
            $offset = 0;

            while (($definition_selection = PatternHelper::selectStringByQuotes($output, "<", ">", $offset, 1))->isValid()) {

                $closure = false;
                if ($output[$definition_selection->getStartPosition()] == "/") {
                    $closure = true;
                }

                $offset = $definition_selection->getEndPosition();

                $keyword = null;

                foreach (self::HTML_KEYWORDS as $find_keyword) {
                    if (!is_bool(PatternHelper::findByText($definition_selection->getReducedString(), $find_keyword))) {
                        $keyword = $find_keyword;
                        break;
                    }
                }

                if ($keyword != null) {

                    $component_object = self::componentToObject($definition_selection->getReducedString());
                    $domtag = substr($component_object[0], strlen($keyword) + $closure);

                    $attributes = self::staticTryGet($component_object[1], []);

                    switch (trim($keyword)) {
                        case "app:":

                            $template = new PypeTemplate($domtag);
                            $component = new PypeComponent($template, $attributes, $context);

                            if ($template->requireClosure()) {

                                $body_selection = BodyFinder::detectBody($output, "app:" . $domtag, $definition_selection->getEndPosition() + 1);
                                $component->setBody($body_selection->getReducedString());
                                $definition_selection->setEndPosition($body_selection->getEndPosition() + strlen("</app:$domtag>"));
                            }

                            $result = $component->render() . "\n";

                            $definition_selection->moveStartPosition(-1);
                            $output = self::writeOnSelection($definition_selection, $output, $result);
                            break;

                        case "this":

                            if (isset($context["this:class"])) {
                                $value = ArrayHelper::parameterReplace($context["this:class"], $context);
                                if ($value != null) {
                                    if (!StringHelper::startsWith($value, "app-")) {
                                        $value = "app-$value";
                                    }
                                    $context["this:class"] = $value;
                                }
                            }

                            $template = new PypeTemplate($context["this"]);

                            $prototype = $template->getPrototype();

                            if (!$closure) {
                                $attributes["class"] = trim($context["this:class"] . " " . self::staticTryGet($attributes["class"], ""));
                                self::parseMultivalueFields($attributes);
                            } else {
                                $prototype = "/" . $prototype;
                            }

                            $definition_selection->moveStartPosition(-1);
                            $output = self::writeOnSelection($definition_selection, $output, new HTMLStrip($prototype, $attributes));

                            break;

                        case "ifdef":

                            if (!$closure) {

                                $check = self::staticTryGet($attributes["check"], "");

                                if (strlen($check) > 0) {

                                    $body_selection = BodyFinder::detectBody($output, "ifdef", $definition_selection->getEndPosition() + 1);
                                    
                                    $ifdef_string = "";
                                    if (isset($context[$check])) {
                                        $ifdef_string = ltrim(self::renderString($body_selection->getReducedString(), $context));
                                    }

                                    $result = trim($ifdef_string);

                                    $definition_selection->moveStartPosition(-1);
                                    $definition_selection->setEndPosition($body_selection->getEndPosition() + strlen("</ifdef>"));
                                    $output = self::writeOnSelection($definition_selection, $output, $result);
                                }
                            }

                            break;

                        case "for":

                            if (!$closure) {

                                $item_name = self::staticTryGet($attributes["name"], "i");

                                if (strlen($item_name) > 0 && PatternHelper::isNumber($attributes["start"]) && PatternHelper::isNumber($attributes["end"])) {

                                    $for_start = (int)$attributes["start"];
                                    $for_end = (int)$attributes["end"];

                                    $body_selection = BodyFinder::detectBody($output, "for", $definition_selection->getEndPosition() + 1);

                                    $for_string = "";
                                    $step = $for_end - $for_start;

                                    if ($step > 0) {
                                        for ($i = $for_start; $i <= $for_end; $i++) {
                                            $context["$item_name"] = $i;
                                            $context["this:random"] = Cryptography::computeRandomKey(8);
                                            $for_string .= ltrim(self::renderString($body_selection->getReducedString(), $context));
                                        }
                                    } else {
                                        for ($i = $for_start; $i >= $for_end; $i--) {
                                            $context["$item_name"] = $i;
                                            $context["this:random"] = Cryptography::computeRandomKey(8);
                                            $for_string .= ltrim(self::renderString($body_selection->getReducedString(), $context));
                                        }
                                    }

                                    $definition_selection->moveStartPosition(-1);
                                    $definition_selection->setEndPosition($body_selection->getEndPosition() + strlen("</for>"));

                                    $result = " " . trim($for_string);
                                    $output = self::writeOnSelection($definition_selection, $output, $result);
                                }
                            }

                            break;

                        case "foreach":

                            if (!$closure) {

                                $item_name = self::staticTryGet($attributes["name"], "");
                                $from_name = self::staticTryGet($attributes["from"], "");

                                if (strlen($item_name) > 0 && strlen($from_name) > 0) {

                                    $body_selection = BodyFinder::detectBody($output, "foreach", $definition_selection->getEndPosition() + 1);
                                    $foreach_string = "";

                                    if (isset($context[$from_name])) {

                                        if (ArrayHelper::is2Dimensional($context[$from_name])) {

                                            foreach ($context[$from_name] as $array => $object) {

                                                $local_context = [];
                                                foreach ($object as $key => $value) {
                                                    $local_context["$item_name:$key"] = $value;
                                                }

                                                $keys = array_keys($context);
                                                foreach ($keys as $key) {
                                                    if (StringHelper::startsWith($key, "$item_name:") && !isset($local_context[$key])) {;
                                                        unset($context[$key]);
                                                    }
                                                }

                                                $context = ArrayHelper::merge2DArray(true, $context, $local_context);

                                                $context[$item_name] = preg_replace('/\s+/', ' ', trim(var_export($object, true)));
                                                $foreach_string .= ltrim(self::renderString($body_selection->getReducedString(), $context));
                                            }
                                        } else {

                                            foreach ($context[$from_name] as $array) {

                                                $context[$item_name] = $array;
                                                $foreach_string .= ltrim(self::renderString($body_selection->getReducedString(), $context));
                                            }
                                        }
                                    }

                                    $definition_selection->moveStartPosition(-1);
                                    $definition_selection->setEndPosition($body_selection->getEndPosition() + strlen("</foreach>"));

                                    $result = " " . trim($foreach_string);
                                    $output = self::writeOnSelection($definition_selection, $output, $result);
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

        while (($definition_selection = PatternHelper::selectStringByQuotes($output, "{?", "}", $offset, 0))->isValid()) {
            $id = substr($definition_selection->getReducedString(), 2);
            if ($id != null && strlen($id) > 0) {
                $initial = self::staticTryGet($context["$id"], "");
                $output = self::writeOnSelection($definition_selection, $output, "<div class=\"app-sync-$id d-inline pr-1\">$initial</div>");
            }
            $offset = $definition_selection->getEndPosition() + 1;
        }

        return $output;
    }

    private static function writeOnSelection(Selection &$definition_selection, &$source, $replace): string
    {
        $pre = substr($source, 0, $definition_selection->getStartPosition());
        $post = substr($source, $definition_selection->getEndPosition() + 1);
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

    public static function componentToObject(string $input): array
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
