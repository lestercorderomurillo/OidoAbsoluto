<?php

namespace Pipeline\PypeEngine;

use Pipeline\HTTP\Server\ServerResponse;
use Pipeline\PypeEngine\HTML\TagStrip;
use Pipeline\PypeEngine\HTML\Selection;
use Pipeline\PypeEngine\HTML\BodyFinder;
use Pipeline\Core\Exceptions\CompileException;
use Pipeline\Security\Cryptography;
use Pipeline\Utilities\Vector;
use Pipeline\Utilities\Pattern;
use Pipeline\Utilities\Text;
use Pipeline\Traits\DefaultAccessorTrait;

use function Pipeline\Kernel\safeGet;

class PypeCompiler
{
    use DefaultAccessorTrait;
    const HTML_KEYWORDS = ["if", "app:", "foreach", "for", "this"];

    public static PypeContextFactory $context_factory;

    public static function setContextFactory(PypeContextFactory $context_factory)
    {
        self::$context_factory = $context_factory;
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

    private static function compileAppTagOnOuputBuffer(string &$stream, string $tag, array $attributes, array $this_context, Selection &$source_selection): string
    {
        $template = PypeTemplateBatch::getTemplate($tag);
        $component = new PypeComponent($template, $attributes, $this_context);

        if (!$template->isInlineComponent()) {

            $body_selection = BodyFinder::detectBody($stream, "app:" . $tag, $source_selection->getEndPosition() + 1);
            $component->setBody($body_selection->getString());
            $source_selection->setEndPosition($body_selection->getEndPosition() + strlen("</app:$tag>"));
        }

        $source_selection->moveStartPosition(-1);
        return PypeCompiler::compileSelection($source_selection, $stream, $component->render());
    }

    public static function removeKeyword(string $tag, string $keyword, bool $closure): string
    {
        return substr($tag, strlen($keyword) + $closure);
    }

    public static function renderString(string $this_html, array $this_context = [], int $depth = 0): string
    {
        PypeCompiler::compileMathEvaluation($this_context);

        try {

            $output = Vector::parameterReplace($this_html, [
                self::$context_factory->getViewContext(),
                self::$context_factory->getSessionContext(),
                $this_context
            ], "{", "}", true);

            $offset = 0;

            while (($definition_selection = Pattern::selectStringByQuotes($output, "<", ">", $offset, 1))->isValid()) {

                $closure = false;
                if ($output[$definition_selection->getStartPosition()] == "/") {
                    $closure = true;
                }

                $offset = $definition_selection->getEndPosition();
                $keyword = null;

                foreach (self::HTML_KEYWORDS as $find_keyword) {
                    if (Text::startsWith($definition_selection->getString(), $find_keyword, $closure)) {
                        $keyword = $find_keyword;
                        break;
                    }
                }

                if ($keyword != null) {
                    $component_object = self::componentStringToArray($definition_selection->getString());
                    $domtag = substr($component_object[0], strlen($keyword) + $closure);
                    $attributes = self::staticTryGet($component_object[1], []);


/*
                    $component_object = PypeCompiler::componentStringToArray($definition_selection->getString());
                    $domtag = PypeCompiler::removeKeyword($component_object[0], $keyword, $closure);
                    $attributes = safeGet($component_object[1], []);
*/
                    switch (trim($keyword)) {

                        case "app:":

                            
                            $template = PypeTemplateBatch::getTemplate($domtag);
                            $component = new PypeComponent($template, $attributes, $this_context);

                            if (!$template->isInlineComponent()) {

                                $body_selection = BodyFinder::detectBody($output, "app:" . $domtag, $definition_selection->getEndPosition() + 1);
                                $component->setBody($body_selection->getString());

                                $definition_selection->setEndPosition($body_selection->getEndPosition() + strlen("</app:$domtag>"));
                            }

                            $result = $component->render();

                            $definition_selection->moveStartPosition(-1);
                            $output = self::compileSelection($definition_selection, $output, $result);

                            //PypeCompiler::compileAppTagOnOuputBuffer($output, $domtag, $attributes, $this_context, $definition_selection);

                            break;

                        case "this":

                            if (isset($this_context["this:class"])) {
                                $value = Vector::parameterReplace($this_context["this:class"], $this_context);
                                if ($value != null) {
                                    if (!Text::startsWith($value, "app-")) {
                                        $value = "app-$value";
                                    }
                                    $this_context["this:class"] = $value;
                                }
                            }

                            $template = PypeTemplateBatch::getTemplate($this_context["this"]);
                            $prototype = $template->getPrototype();

                            if (!$closure) {
                                $attributes["class"] = trim($this_context["this:class"] . " " . self::staticTryGet($attributes["class"], ""));
                            } else {
                                $prototype = "/" . $prototype;
                            }

                            $definition_selection->moveStartPosition(-1);
                            $output = self::compileSelection($definition_selection, $output, new TagStrip($prototype, $attributes));

                            if (!$closure) {
                                $id = self::staticTryGet($attributes["id"], "");
                                if (strlen($id) > 0) {
                                    foreach ($this_context as $key => $value) {
                                        if (is_string($value)) {
                                            $value = "\"$value\"";
                                        }
                                        $key = str_replace("this:", "", $key);
                                        if ($template->hasStatefulKey($key)) {
                                            self::$context_factory->addStatefulScripts("state('$id', '$key', $value);");
                                        }
                                    }
                                }
                            }

                            break;

                        case "if":

                            if (!$closure) {

                                $value = self::staticTryGet($attributes["value"], "");
                                $equal = self::staticTryGet($attributes["equals"], "");
                                $notEqual = self::staticTryGet($attributes["notEquals"], "");
                                $startsWith = self::staticTryGet($attributes["startsWith"], "");
                                $endsWith = self::staticTryGet($attributes["endsWith"], "");

                                PypeCompiler::compileMathEvaluation($equal);
                                PypeCompiler::compileMathEvaluation($notEqual);
                                PypeCompiler::compileMathEvaluation($startsWith);
                                PypeCompiler::compileMathEvaluation($endWith);

                                if (strlen($value) > 0) {

                                    $body_selection = BodyFinder::detectBody($output, "if", $definition_selection->getEndPosition() + 1);

                                    $if_string = "";
                                    $applicable = true;
                                    $compare = "";

                                    $mixed_context = Vector::merge2DArray(
                                        true,
                                        self::$context_factory->getViewContext(),
                                        self::$context_factory->getSessionContext(),
                                        $this_context
                                    );

                                    if (Text::startsWith($value, "{") && Text::endsWith($value, "}")) {
                                        $compare = self::staticTryGet($mixed_context[substr($value, 1, -1)], "");
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

                                $item_name = self::staticTryGet($attributes["name"], "i");

                                PypeCompiler::compileMathEvaluation($item_name);
                                PypeCompiler::compileMathEvaluation($attributes["start"]);
                                PypeCompiler::compileMathEvaluation($attributes["end"]);

                                if (strlen($item_name) > 0 && Pattern::isNumber($attributes["start"]) && Pattern::isNumber($attributes["end"])) {

                                    $for_start = (int)$attributes["start"];
                                    $for_end = (int)$attributes["end"];

                                    $body_selection = BodyFinder::detectBody($output, "for", $definition_selection->getEndPosition() + 1);

                                    $for_string = "";
                                    $step = $for_end - $for_start;

                                    $mixed_context = Vector::merge2DArray(
                                        true,
                                        self::$context_factory->getViewContext(),
                                        self::$context_factory->getSessionContext(),
                                        $this_context
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

                                $item_name = self::staticTryGet($attributes["name"], "");
                                $from_name = self::staticTryGet($attributes["from"], "");

                                $skip = self::staticTryGet($attributes["skip"], 0);
                                $take = self::staticTryGet($attributes["take"], 10000);

                                PypeCompiler::compileMathEvaluation($skip);
                                PypeCompiler::compileMathEvaluation($take);

                                if (strlen($item_name) > 0 && strlen($from_name) > 0) {

                                    $body_selection = BodyFinder::detectBody($output, "foreach", $definition_selection->getEndPosition() + 1);
                                    $foreach_string = "";

                                    $mixed_context = Vector::merge2DArray(
                                        true,
                                        self::$context_factory->getViewContext(),
                                        self::$context_factory->getSessionContext(),
                                        $this_context
                                    );

                                    if (Text::startsWith($from_name, "{") && Text::endsWith($from_name, "}")) {

                                        $from_name = substr($from_name, 1, -1);
                                        if (isset($mixed_context[$from_name])) {

                                            if (Vector::is2Dimensional($mixed_context[$from_name])) {

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

                                                            $mixed_context = Vector::merge2DArray(true, $mixed_context, $local_context);

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
            ServerResponse::create(500, $e->getMessage())->sendAndExit();
        }

        if ($depth == 0) {
            $offset = 0;
            while (($definition_selection = Pattern::selectStringByQuotes($output, "{?", "}", $offset, 0))->isValid()) {
                $id = substr($definition_selection->getString(), 2);
                if ($id != null && strlen($id) > 0) {
                    $initial = self::staticTryGet($this_context["$id"], "");
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

    public static function componentStringToArray(string $input): array
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
