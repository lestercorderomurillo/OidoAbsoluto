<?php

namespace VIP\Renderer;

use Directory;
use VIP\App\App;
use VIP\Core\InternalResult;
use VIP\Component\Template;
use VIP\HTTP\Server\Response\View;
use VIP\Factory\ComponentFactory;
use VIP\Factory\HTMLFactory;
use VIP\FileSystem\BasePath;
use VIP\FileSystem\DirectoryPath;
use VIP\FileSystem\FilePath;
use VIP\Service\AbstractService;
use VIP\FileSystem\FileSystem;
use VIP\Utilities\StringHelper;

class ViewRenderer extends AbstractService
{
    private View $view;

    private string $application_name;

    private int $counter;
    private bool $rendering_closures;

    public function __construct(string $application_name)
    {
        parent::__construct("ViewRenderer");
        $this->application_name = $application_name;
        $this->rendering_closures = false;
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
        $result = $this->compileBody();
        $title = $result->getData("title") . " - " . $this->application_name;
        $output = $this->compileBegin();
        $output .= $this->compileHeaders($title);
        $output .= $result->getData("html");
        $output .= $this->compileEnd();
        echo ($output);
    }

    private function compileBegin(): string
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
        $headers = FileSystem::includeAsString(new FilePath(BasePath::DIR_INCLUDE, "private/layout", "php"));

        if (App::$app->hasHotswapEnabled()) {
            $timestamp = $this->view->getTimestamp();
            $path = (new FilePath(
                BasePath::DIR_VIEWS,
                $this->view->getCallbackControllerName() . "/" .
                $this->view->getViewName()
            , "phtml"))->toString();

            $page = md5($path);
            $headers .= <<<HTML
                \n
                <meta name="page" content="$page">
                <meta name="timestamp" content="$timestamp">
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

    private function compileEnd(): string
    {
        return <<<HTML
        </div>
        <br><br>
        </body>
        </html>
        HTML;
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

    private function compileBody(): InternalResult
    {
        $result = $this->includeDependencies($this->view);
        $result = $this->compileBodyJSDOM($result);
        $result = $this->compileBodyParameters($result);
        $result = $this->compileBodyComponents($result);
        
        return $result;
    }

    private function includeDependencies(): InternalResult
    {
        $html = $this->view->getSourceHTML();
        $js_path = new FilePath($this->view->getDirectory()->getBase() . $this->view->getControllerName() . "/", $this->view->getViewName(), "js");
        if (FileSystem::exists($js_path)) {
            $js = "<script type=\"text/javascript\">" . FileSystem::includeAsString($js_path) . "</script>";
            $html .= "\n$js";
        }

        return new InternalResult(["html" => $html, "view" => $this->view]);
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
