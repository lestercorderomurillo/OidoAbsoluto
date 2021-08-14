<?php

namespace VIP\Renderer;

use VIP\Core\InternalResult;
use VIP\Component\Template;
use VIP\HTTP\Server\Response\View;
use VIP\Factory\ComponentFactory;
use VIP\Factory\HTMLFactory;
use VIP\Service\AbstractService;
use VIP\FileSystem\FileSystem;

class ViewRenderer extends AbstractService
{
    private View $view;

    private string $application_name;
    private string $debugging_mode;

    private int $counter;
    private bool $rendering_closures;

    public function __construct(string $application_name, string $debugging_mode)
    {
        parent::__construct("ViewRenderer");
        $this->application_name = $application_name;
        $this->rendering_closures = false;
        $this->debugging_mode = $debugging_mode;
        $this->counter = 0;

        Template::onStaticLoad();
    }

    public function isRenderingClosures(): bool
    {
        return $this->rendering_closures;
    }

    public function getCurrentCounter(): int
    {
        return $this->counter++;
    }

    public function setView(View $view): void
    {
        $this->view = $view;
    }

    public function getViewData(): array
    {
        return $this->view->getParameters();
    }

    public function execute(): void
    {
        $view = $this->view;
        $result = $this->compileBody($view);
        $title = $result->getData("title") . " - " . $this->application_name;
        $output = $this->compileBegin();
        $output .= $this->compileHeaders($title);
        $output .= $result->getData("html");
        $output .= $this->compileEnd();
        echo ($output);
    }

    private function compileBegin(): string
    {
        return
            "<!DOCTYPE html>\n" .
            "<html lang='en'>\n" .
            "<head>\n" .
            "<meta charset='UTF-8'>\n" .
            "<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
    }

    private function compileHeaders(string $title): string
    {
        $headers = FileSystem::includeAsString(__RDIR__ . "/src/VIP/Include/headers.php");

        return
            "<title>$title</title>\n" .
            $headers . "\n" .
            "</head>\n" .
            "<body>\n" .
            "<br><br>\n" .
            "<div class='container'>\n";
    }

    private function compileEnd(): string
    {
        return
            "\n" .
            "</div>\n" .
            "<br><br>\n" .
            "</body>\n" .
            "</html>\n";
    }

    private function matchVirtualPattern(string $html, string $opening, string $closure): InternalResult
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

        return new InternalResult(["string" => $string, "cursor_start" => $cursor_start, "cursor_end" => $cursor_end]);
    }

    private function fetchNextJSDOM(string $html)
    {
        $jsdom = NULL;
        $result = $this->matchVirtualPattern($html, "%%", "%%");

        if ($result->getData("cursor_start") !== false) {
            $string = $result->getData("string");
            $jsdom = HTMLFactory::create("div", ["class" => "sync-$string d-inline"]);
            $jsdom->appendClosure();
            $jsdom->preRenderAfter("&nbsp;");
        }

        if ($jsdom == NULL) {
            return NULL;
        }

        return new InternalResult([
            "jsdom" => $jsdom,
            "cursor_start" => $result->getData("cursor_start"),
            "cursor_end" => $result->getData("cursor_end") + 1
        ]);
    }

    private function fetchNextComponent(string $html, string $opening)
    {
        $component = NULL;
        $result = $this->matchVirtualPattern($html, $opening, ">");

        if ($result->getData("cursor_start") !== false) {
            if ($opening[1] == "/") {
                $component = ComponentFactory::create("/" . $result->getData("string"));
            } else {
                $component = ComponentFactory::create($result->getData("string"));
            }
        }

        if ($component == NULL) {
            return NULL;
        }
        return new InternalResult([
            "component" => $component,
            "cursor_start" => $result->getData("cursor_start"),
            "cursor_end" => $result->getData("cursor_end")
        ]);
    }

    private function writeInternalResultToHTML(string $html, InternalResult $result, string $render): string
    {
        $pre = substr($html, 0, $result->getData("cursor_start"));
        $comp = $result->getData($render)->render();
        $post = trim(substr($html, $result->getData("cursor_end") + 1));
        $html = $pre . $comp . $post;
        return $html;
    }

    private function compileBody(View $view): InternalResult
    {
        $result = $this->includeDependencies($view);
        $result = $this->compileBodyJSDOM($result);
        $result = $this->compileBodyParameters($result);
        $result = $this->compileBodyComponents($result);
        return $result;
    }

    private function includeDependencies(View $view): InternalResult
    {
        $html = $view->getSourceHTML();
        $js = $view->getDirectoryName() . $view->getViewName() . ".js";
        if (FileSystem::exists($js)) {
            $js = "<script type=\"text/javascript\">" . FileSystem::includeAsString($js) . "</script>";
            $html .= "\n$js";
        }

        return new InternalResult(["html" => $html, "view" => $view]);
    }

    private function compileBodyJSDOM(InternalResult $result): InternalResult
    {
        $html = $result->getData("html");
        while (($jsdom_result = $this->fetchNextJSDOM($html)) != NULL) {
            $html = $this->writeInternalResultToHTML($html, $jsdom_result, "jsdom");
        }
        return new InternalResult(["html" => $html, "view" => $result->getData("view")]);
    }

    private function compileBodyParameters(InternalResult $result): InternalResult
    {
        $html = $result->getData("html");
        foreach ($result->getData("view")->getParameters() as $key => $value) {
            if (!is_array($value)) {
                $html = str_replace("%$key%", "$value", "$html");
            }
        }
        return new InternalResult(["html" => $html]);
    }

    private function compileBodyComponents(InternalResult $result): InternalResult
    {
        $title = "No Title";
        $html = $result->getData("html");

        while (($component_result = $this->fetchNextComponent($html, "<v-")) != NULL) {

            $html = $this->writeInternalResultToHTML($html, $component_result, "component");
            $obj = $component_result->getData("component");

            if ($obj->getDOMTag() == "view") {
                if ($obj->hasAttribute("title")) {
                    $title = $obj->getAttribute("title");
                }
            }
        }

        $this->rendering_closures = true;

        while (($component_result = $this->fetchNextComponent($html, "</v-")) != NULL) {
            $html = $this->writeInternalResultToHTML($html, $component_result, "component");
        }

        return new InternalResult(["html" => $html, "title" => $title]);
    }
}
