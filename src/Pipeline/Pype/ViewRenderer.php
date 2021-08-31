<?php

namespace Pipeline\Pype;

use Pipeline\Core\FunctionPipeline;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\BasePath;
use Pipeline\FileSystem\Path\Local\FilePath;
use Pipeline\Pype\Component\Component;
use Pipeline\Pype\Component\HTMLObject;
use Pipeline\Pype\Template\ComponentDefinition;
use Pipeline\Traits\DefaultAccessorTrait;
use Pipeline\Utilities\ArrayHelper;
use function Pipeline\Accessors\App;
use function Pipeline\Accessors\Configuration;

class ViewRenderer
{
    use DefaultAccessorTrait;

    private View $view;
    private static View $contextless_view;

    private string $application_name;

    private int $counter;
    private bool $rendering_closures;
    private array $meta_tags;

    const DOM_BEGIN = "<v-";
    const DOM_END = "</v-";

    public function __construct()
    {
        ComponentDefinition::__initialize();
        $this->application_name = Configuration("application.name");

        $this->counter = 0;
        $this->meta_tags = [];
        $this->rendering_closures = false;
    }

    public function isRenderingClosures(): bool
    {
        return $this->rendering_closures;
    }

    public function getCurrentCounter(): int
    {
        return $this->counter++;
    }

    public function getView(): View
    {
        return $this->view;
    }

    public static function getContextlessView(): View
    {
        return self::$contextless_view;
    }

    public function setView(View $view): ViewRenderer
    {
        $this->view = $view;
        if (!isset(self::$contextless_view)) {
            self::$contextless_view = $view;
        }
        return $this;
    }

    public function renderView(): string
    {
        $fragment = $this->compileBody();
        $title = $fragment->get("title") . " - " . $this->application_name;
        $output = $this->compileHTMLBeggining();
        $output .= $this->compileHeaders($title);
        $output .= $fragment->get("html");
        $output .= $this->compileHTMLEnding();

        return $output;
    }

    public function addMetaTags(array $meta_tags): void
    {
        $this->meta_tags = ArrayHelper::mergeNamedValues($this->meta_tags, $meta_tags);
    }

    public function addMetaTag(string $meta, string $value): void
    {
        $this->meta_tags[$meta] = $value;
    }

    private function compileHTMLBeggining(): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang='en'>
        <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        HTML;
    }

    private function compileHeaders(string $title): string
    {
        $headers = FileSystem::includeAsString(new FilePath(BasePath::DIR_INCLUDE, "Private/PageKernel", "php"));

        if (App()->getRuntimeEnvironment()->hasHotswapEnabled()) {

            $timestamp = $this->view->getTimestamp();
            $page = $this->view->getViewHashIdentifier();

            $timestamp = $this->tryGet($this->meta_tags["timestamp"], $timestamp);
            $page = $this->tryGet($this->meta_tags["page"], $page);

            $headers .= <<<HTML
            \n
            <meta name="timestamp" content="$timestamp">
            <meta name="page" content="$page">
            HTML;
        }

        return <<<HTML
        <title>
            $title
        </title>
            $headers
        </head>
        <body>
        <br><br>
        <div class='container'>\n
        HTML;
    }

    private function compileHTMLEnding(): string
    {
        return <<<HTML
        </div>
        <br><br>
        </body>
        </html>
        HTML;
    }

    private function matchVirtualPattern(string $html, string $opening, string $closure): DataFragment
    {
        $string = NULL;
        $cursor_start = strpos($html, $opening);
        $cursor_end = false;
        if ($cursor_start !== false) {
            $cursor = $cursor_start + strlen($opening);
            $cursor_end = strpos($html, $closure, $cursor);
            $len = $cursor_end - $cursor;
            $string = substr($html, $cursor, $len);
        }

        return new DataFragment(
            [
                "string" => $string,
                "cursor_start" => $cursor_start,
                "cursor_end" => $cursor_end
            ]
        );
    }

    private function fetchNextJSDOM(string $html)
    {
        $jsdom = NULL;
        $fragment = $this->matchVirtualPattern($html, "%%", "%%");

        if ($fragment->get("cursor_start") !== false) {
            $string = $fragment->get("string");

            $jsdom = HTMLObject::create("div", ["class" => "sync-$string d-inline"]);
            $jsdom->appendClosure();
            $jsdom->preRenderAfter("&nbsp;");
        }

        if ($jsdom == NULL) {
            return NULL;
        }

        return new DataFragment([
            "jsdom" => $jsdom,
            "cursor_start" => $fragment->get("cursor_start"),
            "cursor_end" => $fragment->get("cursor_end") + 1
        ]);
    }

    private function fetchNextComponent(string $html, string $opening)
    {
        $component = NULL;
        $fragment = $this->matchVirtualPattern($html, $opening, ">");

        if ($fragment->get("cursor_start") !== false) {

            if ($opening[1] == "/") {
                $component = Component::create("/" . $fragment->get("string"));
            } else {
                $component = Component::create($fragment->get("string"));
            }
            
        }

        if ($component == NULL) {
            return NULL;
        }
        
        return new DataFragment([
            "component" => $component,
            "cursor_start" => $fragment->get("cursor_start"),
            "cursor_end" => $fragment->get("cursor_end")
        ]);
    }

    private function writeFragmentAsHTML(string $html, DataFragment $fragment, string $expected_type): string
    {
        $pre = substr($html, 0, $fragment->get("cursor_start"));
        $comp = $fragment->get($expected_type)->render();
        $post = trim(substr($html, $fragment->get("cursor_end") + 1));

        return $pre . $comp . $post;
    }

    private function compileBody(): ViewFragment
    {
        $result = $this->includeJavascriptScripts($this->view);

        $pipeline = new FunctionPipeline(["compileBodyJSDOM", "compileBodyParameters", "compileBodyComponents"]);
        $result = $pipeline->execute($this, $result);

        return $result;
    }

    private function includeJavascriptScripts(): ViewFragment
    {
        $html = $this->view->getSourceHTML();
        $base_path = $this->view->getDirectory()->getBase() . $this->view->getControllerName() . "/";
        $js_path = new FilePath($base_path, $this->view->getViewName(), "js");

        if (FileSystem::exists($js_path)) {
            $js = "\n<script type=\"text/javascript\">\n" . FileSystem::includeAsString($js_path) . "\n</script>\n";
            $html .= $js;
        }

        return new ViewFragment(["html" => $html, "view" => $this->view]);
    }

    public function compileBodyJSDOM(ViewFragment $result): ViewFragment
    {
        $html = $result->get("html");

        while (($data_fragment = $this->fetchNextJSDOM($html)) != NULL) {
            $html = $this->writeFragmentAsHTML($html, $data_fragment, "jsdom");
        }

        return new ViewFragment(["html" => $html, "view" => $result->get("view")]);
    }

    public function compileBodyParameters(ViewFragment $view_fragment): ViewFragment
    {
        $html = $view_fragment->get("html");

        foreach (($view_fragment->get("view"))->getViewData() as $key => $value) {
            if (!is_array($value)) {
                $html = str_replace("%$key%", "$value", "$html");
            }
        }

        return new ViewFragment(["html" => $html]);
    }

    public function compileBodyComponents(ViewFragment $view_fragment): ViewFragment
    {
        $title = "No Title";
        $html = $view_fragment->get("html");

        while (($data_fragment = $this->fetchNextComponent($html, self::DOM_BEGIN)) != NULL) {

            $html = $this->writeFragmentAsHTML($html, $data_fragment, "component");

            /* To get vendor attributes */
            $obj = $data_fragment->get("component");
            if ($obj->getDOMTag() == "view") {
                if ($obj->hasAttribute("title")) {
                    $title = $obj->getAttribute("title");
                }
            }
        }

        $this->rendering_closures = true;

        while (($data_fragment = $this->fetchNextComponent($html, self::DOM_END)) != NULL) {
            $html = $this->writeFragmentAsHTML($html, $data_fragment, "component");
        }

        return new ViewFragment(["html" => $html, "title" => $title]);
    }
}
