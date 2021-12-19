<?php

namespace Cosmic\DOM;

use Cosmic\Core\DI;
use Cosmic\Core\Exceptions\CompileException;
use Cosmic\DOM\HTML\BodyFinder;
use Cosmic\DOM\HTML\Selection;
use Cosmic\DOM\HTML\TagStrip;
use Cosmic\HTTP\Server\Response;
use Cosmic\Security\Cryptography;
use Cosmic\Utilities\Collection;
use Cosmic\Utilities\Pattern;
use Cosmic\Utilities\Text;

use function Cosmic\Kernel\app;
use function Cosmic\Kernel\safe;

class Compiler
{
    const HTML_KEYWORDS = ["if", "app:", "foreach", "for", "this"];

    public static PypeDOM $pypeDOM;

    public static function setPypeDOM(PypeDOM $pypeDOM)
    {
        self::$pypeDOM = $pypeDOM;
    }

    public static function compileMathEvaluation(&$context): void
    {
        if (is_string($context)) {
            if (Text::startsWith($context, "(") && Text::endsWith($context, ")")) {
                $context = math_eval($context);
            }
        } else if (is_array($context)) {
            foreach ($context as $key => $value) {
                if (is_string($value)) {
                    if (Text::startsWith($value, "(") && Text::endsWith($value, ")")) {
                        $context[$key] = math_eval($value);
                    }
                }
            }
        }
    }

    private static function compileAppTagOnOuputBuffer(string &$stream, string $tag, array $attributes, array $context, Selection &$source_selection): string
    {
        $component = self::$pypeDOM->getComponent($tag);

        $element = app()->create(Element::class);

        $element->setComponent($component);
        $element->setAttributes($attributes);
        $element->setInheritContext($context);

        if (!$component->isInlineComponent()) {

            $body_selection = BodyFinder::detectBody($stream, "app:" . $tag, $source_selection->getEndPosition() + 1);
            $element->setBody($body_selection->getString());
            $source_selection->setEndPosition($body_selection->getEndPosition() + strlen("</app:$tag>"));
        }

        $source_selection->moveStartPosition(-1);
        return self::compileSelection($source_selection, $stream, $element->render());
    }

    public static function removeKeyword(string $tag, string $keyword, bool $closure): ?string
    {
        return substr($tag, strlen($keyword) + $closure);
    }

    public static function renderString(string $this_html, array $context = [], int $depth = 0): string
    {
        self::compileMathEvaluation($context);

        try {

            $output = Pattern::substituteTokens($this_html, [
                self::$pypeDOM->getViewContext(),
                self::$pypeDOM->getSessionContext(),
                $context
            ], "{", "}", true);

            $offset = 0;

            while (($definition_selection = Pattern::selectStringByQuotes($output, "<", ">", $offset, 1))->isValid()) {

                $closure = false;

                if ($output[$definition_selection->getStartPosition()] == "/") {
                    $closure = true;
                }

                $offset = $definition_selection->getEndPosition();
                $TEST = $definition_selection->getString();
                $keyword = null;

                foreach (self::HTML_KEYWORDS as $find_keyword) {
                    if (Text::startsWith($definition_selection->getString(), $find_keyword, $closure)) {
                        $keyword = $find_keyword;
                        break;
                    }
                }

                if ($keyword != null) {
                    
                    $element_object = self::elementStringToArray($definition_selection->getString());

                    $domtag = self::removeKeyword($element_object[0], $keyword, $closure);
                    $attributes = safe($element_object[1], []);

                    switch (trim($keyword)) {
                        case "app:":

                            $component = self::$pypeDOM->getComponent($domtag);

                            $element = PypeDOM::createElement($component, $attributes, $context);

                            if (!$component->isInlineComponent()) {

                                $body_selection = BodyFinder::detectBody($output, "app:" . $domtag, $definition_selection->getEndPosition() + 1);
                                $element->setBody($body_selection->getString());

                                $definition_selection->setEndPosition($body_selection->getEndPosition() + strlen("</app:$domtag>"));
                            }

                            $definition_selection->moveStartPosition(-1);
                            $output = self::compileSelection($definition_selection, $output, $element->render());

                            //self::compileAppTagOnOuputBuffer($output, $domtag, $attributes, $context, $definition_selection);

                            break;

                        case "this":

                            if (isset($context["this:class"])) {
                                $value = Pattern::substituteTokens($context["this:class"], $context);
                                if ($value != null) {
                                    if (!Text::startsWith($value, "app-")) {
                                        $value = "app-$value";
                                    }
                                    $context["this:class"] = $value;
                                }
                            }

                            $component = self::$pypeDOM->getComponent($context["this"]);
                            $prototype = $component->getPrototype();

                            if (!$closure) {
                                $attributes["class"] = trim($context["this:class"] . " " . safe($attributes["class"], ""));
                            } else {
                                $prototype = "/" . $prototype;
                            }

                            $definition_selection->moveStartPosition(-1);
                            $output = self::compileSelection($definition_selection, $output, new TagStrip($prototype, $attributes));

                            if (!$closure) {

                                $id = safe($attributes["id"], "");

                                if (strlen($id) > 0) {
                                    foreach ($context as $key => $value) {
                                        if (is_string($value)) {
                                            $value = "\"$value\"";
                                        }
                                        $key = str_replace("this:", "", $key);
                                        if ($component->hasStatefulKey($key)) {
                                            self::$pypeDOM->registerStatefulScript("state('$id', '$key', $value);");
                                        }
                                    }
                                }
                            }

                            break;

                        case "if":

                            if (!$closure) {

                                $value = safe($attributes["value"], "");
                                $equal = safe($attributes["equals"], "");
                                $notEqual = safe($attributes["notEquals"], "");
                                $startsWith = safe($attributes["startsWith"], "");
                                $endsWith = safe($attributes["endsWith"], "");

                                self::compileMathEvaluation($equal);
                                self::compileMathEvaluation($notEqual);
                                self::compileMathEvaluation($startsWith);
                                self::compileMathEvaluation($endWith);

                                if (strlen($value) > 0) {

                                    $body_selection = BodyFinder::detectBody($output, "if", $definition_selection->getEndPosition() + 1);

                                    $if_string = "";
                                    $applicable = true;
                                    $compare = "";

                                    $mixed_context = Collection::mergeDictionary(
                                        self::$pypeDOM->getViewContext(),
                                        self::$pypeDOM->getSessionContext(),
                                        $context
                                    );

                                    if (Text::startsWith($value, "{") && Text::endsWith($value, "}")) {
                                        $compare = safe($mixed_context[substr($value, 1, -1)], "");
                                    } else if (Text::contains($value, "{") && Text::contains($value, "}")) {
                                        $applicable = false;
                                    } else {
                                        $compare = $value;
                                    }

                                    if (isset($compare) && strlen($compare) > 0) {

                                        if (strlen($equal) > 0) {
                                            if ($compare != $equal) {
                                                $applicable = false;
                                            }
                                        }

                                        if (strlen($notEqual) > 0) {
                                            if ($compare == $notEqual) {
                                                $applicable = false;
                                            }
                                        }

                                        if (strlen($startsWith) > 0) {
                                            if (!Text::startsWith($compare, $startsWith)) {
                                                $applicable = false;
                                            }
                                        }

                                        if (strlen($endsWith) > 0) {
                                            if (!Text::endsWith($compare, $endsWith)) {
                                                $applicable = false;
                                            }
                                        }

                                        if ($applicable) {
                                            $if_string = ltrim(self::renderString($body_selection->getString(), $mixed_context, ++$depth));
                                        }
                                    }

                                    $result = trim($if_string);

                                    $definition_selection->moveStartPosition(-1);
                                    $definition_selection->setEndPosition($body_selection->getEndPosition() + strlen("</if>"));
                                    $output = self::compileSelection($definition_selection, $output, $result);
                                }
                            }

                            break;

                        case "for":

                            if (!$closure) {

                                $item_name = safe($attributes["name"], "i");

                                self::compileMathEvaluation($item_name);
                                self::compileMathEvaluation($attributes["start"]);
                                self::compileMathEvaluation($attributes["end"]);

                                if (strlen($item_name) > 0 && Pattern::isNumber($attributes["start"]) && Pattern::isNumber($attributes["end"])) {

                                    $for_start = (int)$attributes["start"];
                                    $for_end = (int)$attributes["end"];

                                    $body_selection = BodyFinder::detectBody($output, "for", $definition_selection->getEndPosition() + 1);

                                    $for_string = "";
                                    $step = $for_end - $for_start;

                                    $mixed_context = Collection::mergeDictionary(
                                        self::$pypeDOM->getViewContext(),
                                        self::$pypeDOM->getSessionContext(),
                                        $context
                                    );

                                    if ($step > 0) {
                                        for ($i = $for_start; $i <= $for_end; $i++) {
                                            $mixed_context["$item_name"] = $i;
                                            $mixed_context["this:random"] = Cryptography::computeRandomKey(8);
                                            $for_string .= ltrim(self::renderString($body_selection->getString(), $mixed_context, ++$depth));
                                        }
                                    } else {
                                        for ($i = $for_start; $i >= $for_end; $i--) {
                                            $mixed_context["$item_name"] = $i;
                                            $mixed_context["this:random"] = Cryptography::computeRandomKey(8);
                                            $for_string .= ltrim(self::renderString($body_selection->getString(), $mixed_context, ++$depth));
                                        }
                                    }

                                    $result = " " . trim($for_string);

                                    $definition_selection->moveStartPosition(-1);
                                    $definition_selection->setEndPosition($body_selection->getEndPosition() + strlen("</for>"));
                                    $output = self::compileSelection($definition_selection, $output, $result);
                                }
                            }

                            break;

                        case "foreach":

                            if (!$closure) {

                                $item_name = safe($attributes["name"], "");
                                $from_name = safe($attributes["from"], "");

                                $skip = safe($attributes["skip"], 0);
                                $take = safe($attributes["take"], 10000);

                                self::compileMathEvaluation($skip);
                                self::compileMathEvaluation($take);

                                if (strlen($item_name) > 0 && strlen($from_name) > 0) {

                                    $body_selection = BodyFinder::detectBody($output, "foreach", $definition_selection->getEndPosition() + 1);
                                    $foreach_string = "";

                                    $mixed_context = Collection::mergeDictionary(
                                        self::$pypeDOM->getViewContext(),
                                        self::$pypeDOM->getSessionContext(),
                                        $context
                                    );

                                    if (Text::startsWith($from_name, "{") && Text::endsWith($from_name, "}")) {

                                        $from_name = substr($from_name, 1, -1);
                                        if (isset($mixed_context[$from_name])) {

                                            if (Collection::is2Dimensional($mixed_context[$from_name])) {

                                                $skipped = 0;
                                                $taken = 0;

                                                foreach ($mixed_context[$from_name] as $array => $object) {

                                                    $local_context = [];

                                                    if ($skipped < $skip) {
                                                        $skipped++;
                                                    } else {

                                                        if ($taken < $take) {

                                                            $taken++;

                                                            foreach ($object as $key => $value) {
                                                                $local_context["$item_name:$key"] = $value;
                                                            }

                                                            $keys = array_keys($mixed_context);
                                                            foreach ($keys as $key) {
                                                                if (Text::startsWith($key, "$item_name:") && !isset($local_context[$key])) {;
                                                                    unset($mixed_context[$key]);
                                                                }
                                                            }

                                                            $mixed_context = Collection::mergeDictionary($mixed_context, $local_context);

                                                            $mixed_context[$item_name] = preg_replace('/\s+/', ' ', trim(var_export($object, true)));
                                                            $foreach_string .= ltrim(self::renderString($body_selection->getString(), $mixed_context, ++$depth));
                                                        }
                                                    }
                                                }
                                            } else {

                                                foreach ($mixed_context[$from_name] as $array) {

                                                    $mixed_context[$item_name] = $array;
                                                    $foreach_string .= ltrim(self::renderString($body_selection->getString(), $mixed_context, ++$depth));
                                                }
                                            }
                                        }

                                        $definition_selection->moveStartPosition(-1);
                                        $definition_selection->setEndPosition($body_selection->getEndPosition() + strlen("</foreach>"));

                                        $result = " " . trim($foreach_string);
                                        $output = self::compileSelection($definition_selection, $output, $result);
                                    } else {
                                        throw new CompileException("\"Foreach\" need a {object} reference in the \"from\" attribute to work.");
                                    }
                                } else {
                                    throw new CompileException("\"Foreach\" is missing \"name\" or \"from\" parameters.");
                                }
                            }

                            break;
                    }
                }
            }
        } catch (\Exception $e) {
            Response::create(500, $e->getMessage())->sendAndExit();
        }

        if ($depth == 0) {
            $offset = 0;
            while (($definition_selection = Pattern::selectStringByQuotes($output, "{?", "}", $offset, 0))->isValid()) {
                $id = substr($definition_selection->getString(), 2);
                if ($id != null && strlen($id) > 0) {
                    $initial = safe($context["$id"], "");
                    $output = self::compileSelection($definition_selection, $output, "<div class=\"app-sync-$id d-inline pr-1\">$initial</div>");
                }
                $offset = $definition_selection->getEndPosition() + 1;
            }
        }

        return $output;
    }

    public static function compileSelection(Selection &$selection, string &$stream, string $replace_string): string
    {
        $pre_selection_string = substr($stream, 0, $selection->getStartPosition());
        $post_selection_string = substr($stream, $selection->getEndPosition() + 1);
        return $pre_selection_string . $replace_string . $post_selection_string;
    }

    public static function elementStringToArray(string $input): array
    {
        $input = preg_replace('!\s+!', ' ', $input);
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
        return [$tag, $attributes];
    }
}
