<?php

namespace Cosmic\Binder;

use Cosmic\Binder\Exceptions\CompileException;
use Cosmic\Binder\HTML\Beautifier;
use Cosmic\Binder\HTML\TagStrip;
use Cosmic\FileSystem\FileSystem;
use Cosmic\FileSystem\Paths\File;
use Cosmic\FileSystem\Paths\Folder;
use Cosmic\Utilities\Collection;
use Cosmic\Utilities\HTML;
use Cosmic\Utilities\Pattern;
use Cosmic\Utilities\Selection;
use Cosmic\Utilities\Text;
use Cosmic\Utilities\Transport;
use ScssPhp\ScssPhp\Compiler as ScssPhpCompiler;
use ScssPhp\ScssPhp\Exception\CompilerException;
use LangleyFoxall\MathEval\MathEvaluator;

class Compiler
{
    /**
     * @var Beautifier $beatifier Used by the Compiler to parse HTML identation.
     */
    private Beautifier $beatifier;

    /**
     * Constructor. 
     */
    public function __construct()
    {
        $this->beatifier = new Beautifier();
    }

    /**
     * Compile a phps file into a valid php file.
     * 
     * @return string The compiled PHPS file.
     */
    public function precompileFile(File $phpsFile): string
    {
        $value = FileSystem::read($phpsFile);

        $value = strtr($value, ['{{' => "<<<'HTML'", "HTML" => "HTML"]);
        $value = preg_replace('/^[\n\r]+/m', '$1', $value);

        return preg_replace('/<\?php|\?>/', '$1 ', $value);
    }

    /**
     * Compile all the sass files and generate the output build.css file.
     * 
     * @return void 
     */
    public function compileStylesheet()
    {
        if (configuration("framework.compileStylesheet")) {

            // Preparations
            $scssCompiler = new ScssPhpCompiler();
            $outputFile = new File(__CONTENT__ . "Output/build.css");

            // Content loading
            $baseContent = FileSystem::read(new File(__CONTENT__ . "page.scss")) . "\n\n";

            // Get prefixes files
            $packages = FileSystem::find(new Folder("src/Cosmic/Bundle/Packages/"), ["scss"]);

            // Get components files
            $files = Collection::mergeList($packages, app()->get(DOM::class)->getSCSSFiles());

            // Read all files
            foreach ($files as $file) {
                $baseContent .= FileSystem::read($file) . "\n\n";
            }

            $build = $scssCompiler->compileString($baseContent);

            FileSystem::write($outputFile, "/* AUTOGENERATED CSS FILE. DO NOT EDIT. */ \n" . $build->getCss(), 0);
        }
    }

    /**
     * Compile a selection into the html string. Selection can then replace only a part of the string with another one.
     * 
     * @param string $html The selection to compile.
     * @param Selection $selection The selection to compile.
     * @param string $replaceString The selection to compile.
     * 
     * @return string The new HTML content string with the applied replacement.
     */
    public static function compileSelection(string $html, Selection $selection, string $replaceString): string
    {
        $preSelectionString = substr($html, 0, $selection->getStartPosition());
        $postSelectionString = substr($html, $selection->getEndPosition());
        return $preSelectionString . $replaceString . $postSelectionString;
    }

    /**
     * Compile a raw cosmic string to a valid compiled HTML string.
     * 
     * @param string $html The COSMIC HTML string.
     * @param array $tokens [Optional] A collection of tokens to use as replacements.
     * @param int $depth [Optional] The recursive depth level.
     * 
     * @return string The compiled HTML string.
     */
    public function compileString(string $html, array $tokens = [], int $depth = 0): string
    {
        $offset = 0;

        $html = $this->beatifier->prefixIndent($html);

        $html = $this->compileServerSideTokens($html, $tokens);
        $html = $this->compileMathExpression($html);

        while (($selection = Pattern::select($html, "<", ">", $offset))->isValid()) {

            $elementString = $selection->getString();
            $offset++;

            $isCommentTag = ($elementString[0] == "!") ? true : false;

            if (!$isCommentTag) {
                $isCloseTag = ($elementString[0] == "/") ? true : false;

                if ($isCloseTag) {
                    $elementString = substr($elementString, 1);
                }

                if (!$isCloseTag) {

                    if ($this->isGenericElement($elementString)) {

                        $data = $this->extractRawData($elementString);
                        $strip = new TagStrip($data[0], $data[1]);
                        $html = $this->compileSelection($html, $selection, $strip);
                    } else {

                        $element = $this->createElementFromString($elementString);

                        $component = $element->getComponentInstance();

                        $elementComponentName = $element->getComponentName();

                        if ($component->isInlineComponent()) {

                            $component->body = __EMPTY__;
                        } else {

                            $bodySelection = HTML::findElementBody($html, $elementComponentName, $selection->getEndPosition());

                            if ($bodySelection == null) {
                                throw new CompilerException("The element with the component '" . $component->getClassName() . "' has no body in the template/view, or it's not marked as an inline component");
                            }

                            $bodySelection->moveStartPosition(-1);
                            $bodySelection->moveEndPosition(1);

                            $component->body = $bodySelection->toString();
                        }

                        $prefix = $component->getSimplifiedName() . "_" . $component->id . "_";

                        $componentTemplate = $component->getRenderTemplate();
                        $componentTemplate = $this->compileRenderTemplateEvents($componentTemplate, $prefix);
                        $componentTemplate = $this->compileClientSideTokens($componentTemplate, $component->id);

                        $componentTokens = get_object_vars($component);

                        if ($component->isInlineComponent()) {

                            $passdownTokens = Collection::mergeDictionary($tokens, $componentTokens);
                            $selection->setEndPosition($selection->getEndPosition());
                        } else {

                            $passdownTokens = Collection::mergeDictionary($tokens, $componentTokens, ["body" => $component->body]);
                            $selection->setEndPosition($bodySelection->getEndPosition() + strlen("</$elementComponentName>"));
                        }

                        $output = $this->compileString($componentTemplate, $passdownTokens, $depth + 1);
                        $component->tryDispose();

                        $html = $this->compileSelection($html, $selection, $output);
                    }
                }
            }
        }

        if ($depth == 0) {
            $html = "<!DOCTYPE html>\n" . $this->beatifier->beautifyString($html);
        }

        return $html;
    }

    /**
     * Compile all events for this render template.
     * 
     * @param string $html The string to be compiled.
     * @param string $prefix The prefix for the events.
     * 
     * @return string The token-compiled string.
     */
    public function compileRenderTemplateEvents(string $html, string $prefix): string
    {
        $html = preg_replace("/(?= *)extern\.(?=[A-z0-9_]*\()/", __EMPTY__, $html);
        $html = preg_replace("/(?= *)component\.(?=[A-z0-9_]*\()/", $prefix, $html);
        $html =  preg_replace('/(\([a-z]+\)=")([^"]*)(?=")/', '$1' . $prefix . "$2", $html);

        return $html;
    }

    /**
     * Compile all client side tokens for javascript use.
     * 
     * @param string $html The string to be compiled.
     * @param string $componentId The ID of the component.
     * 
     * @return string The token-compiled string.
     */
    public function compileClientSideTokens(string $html, string $componentId = "_globalComponent_"): string
    {
        $offset = 0;

        while (($selection = Pattern::select($html, "{?", "}", $offset))->isValid()) {

            $tokenRaw = trim($selection->getString());
            $html = $this->compileSelection($html, $selection, "<div id='$componentId.$tokenRaw' style='display: inline;'></div>");
            $offset++;
        }

        return $html;
    }

    /**
     * Compile all the tokens in the given string.
     * 
     * @param string $html The string to be compiled.
     * @param array $tokens A collection of tokens to use as replacements.
     * 
     * @return string The token-compiled string.
     */
    public function compileServerSideTokens(string $html, array $tokens = []): string
    {
        $offset = 0;

        while (($selection = Pattern::select($html, "{", "}", $offset))->isValid()) {

            $tokenRaw = trim($selection->getString());

            if (strlen($tokenRaw) > 0 && $tokenRaw[0] != "?" && $tokenRaw[0] != '"' && isset($tokens[$tokenRaw])) {

                if (is_array($tokens[$tokenRaw])) {
                    $compiledValue = Transport::arrayToString($tokens[$tokenRaw]);
                } else {
                    $compiledValue = $tokens[$tokenRaw];
                }

                $html = $this->compileSelection($html, $selection, $compiledValue);
            }

            $offset++;
        }

        return $html;
    }

    /**
     * Compile a raw cosmic string to a valid compiled HTML string.
     * 
     * @param string[] $array The strings to be compiled (list).
     * @param array $tokens A collection of tokens to use as replacements.
     * 
     * @return array The list of token-compiled string.
     */
    public function compileServerSideTokensInArray(array $array, array $tokens = []): array
    {
        foreach ($array as $line) {
            $output[] = $this->compileServerSideTokens($line, $tokens);
        }
        return $output;
    }

    /**
     * Extract the dom tag and attributes from the given string.
     * 
     * @return array An array with the tag at index 0 and attributes at index 1.
     */
    public static function extractRawData(string $input): array
    {
        $raw = explode(" ", trim($input), 2);

        $outputName = $raw[0];
        $outputAttributes = [];

        if (isset($raw[1])) {

            $attributes = Text::quotedExplode($raw[1]);

            foreach ($attributes as $attribute) {

                $attribute = Text::multiExplode(["='", "=\""], $attribute);

                $key = $attribute[0];
                $value = (isset($attribute[1])) ? $attribute[1] : null;

                if (isset($value)) {
                    if ($value[strlen($value) - 1] == "\"" || $value[strlen($value) - 1] == "'") {
                        $value = substr($value, 0, -1);
                    } else {
                        throw new CompileException("Cannot parse key value from string object: $value");
                    }
                }

                $outputAttributes[$key] = $value;
            }
        }

        return [$outputName, $outputAttributes];
    }

    /**
     * Compile a raw cosmic element into an instance of Element ready to be used.
     * 
     * @return Element The element instance.
     */
    public static function createElementFromString(string $input): Element
    {
        $data = self::extractRawData($input);
        return new Element($data[0], $data[1]);
    }

    /**
     * Compile all math expressions inside the HTML string.
     * 
     * @param string $html The HTML string to compile.
     * 
     * @return string The HTML string compiled.
     */
    public function compileMathExpression(string $html): string
    {
        $offset = 0;

        while (($selection = Pattern::select($html, "[", "]", $offset))->isValid()) {

            $expressionRaw = trim($selection->getString(true));

            if (!Text::contains($expressionRaw, ["{", "}", '"'])) {
                $number = (float)number_format(math_eval($expressionRaw), 2);
                $html = $this->compileSelection($html, $selection, $number);
            }

            $offset++;
        }

        return $html;
    }

    /**
     * Convert the given input string from camelCase to dashed-case.
     *
     * @param array $attributes The input string to convert.
     *
     * @return array The string converted to dashed case.
     */
    public function compileMultivalueAttributes(array $attributes): array
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

        return $attributes;
    }


    /**
     * Return if the given raw input is a cosmic element or a HTML generic element.
     * 
     * @param string $raw The raw whole tag.
     * 
     * @return bool True if it's a standart HTML dom element, false otherwise.
     */
    public static function isGenericElement(string $raw): string
    {
        return !preg_match('/^\p{Lu}/u', $raw);
    }
}
