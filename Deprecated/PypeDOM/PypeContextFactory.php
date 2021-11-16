<?php

namespace Pipeline\PypeDOM;

use Pipeline\Core\Types\View;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\Path;
use Pipeline\FileSystem\Path\ServerPath;
use Pipeline\Security\Cryptography;
use Pipeline\Utilities\Collection;
use function Pipeline\Kernel\session;

class PypeContextFactory
{
    private array $packages = [];
    private array $scripts = [];
    private array $styles = [];

    private array $components_scripts = [];
    private array $components_awake_scripts = [];
    private array $components_stateful_scripts = [];

    private string $view_identifier;
    private string $view_timestamp;
    private string $view_script;

    private array $view_context = [];
    private array $session_context = [];

    public function addAwakeScripts(string $compiled): void
    {
        $this->components_awake_scripts[] = $compiled;
    }

    public function addStatefulScripts(string $compiled): void
    {
        $this->components_stateful_scripts[] = $compiled;
    }

    public function getCompiledAwakeScripts(): string
    {
        $space = str_repeat(' ', 4);
        $composed = "";
        foreach ($this->components_awake_scripts as $script) {
            $composed .= $script . "\n";
        }
        if (strlen($composed) > 0) {
            $composed =
                <<<HTML
            <script type="text/javascript">
            $(function() {
            $composed
            });
            </script>
            HTML;
        }
        return $composed;
    }

    public function getCompiledStatefulScripts(): string
    {
        $space = str_repeat(' ', 4);
        $composed = "";
        foreach ($this->components_stateful_scripts as $script) {
            $composed .= $space . $script . "\n";
        }
        if (strlen($composed) > 0) {
            $composed =
                <<<HTML
            <script type="text/javascript">
            $(function() {
            $composed
            });
            </script>
            HTML;
        }
        return $composed;
    }

    public function getCompiledComponentScripts(): string
    {
        $composed = "";
        foreach ($this->components_scripts as $script) {
            $composed .= $script . "\n";
        }
        if (strlen($composed) > 0) {
            $composed =
                <<<HTML
            <script type="text/javascript">
            $composed
            </script>
            HTML;
        }
        return $composed;
    }

    public function getComponentsScripts(): array
    {
        return $this->components_scripts;
    }

    public function __construct(View &$view)
    {
        $this->buildPackagesContext();
        $this->buildStyleContext();
        $this->buildScriptContext();
        $this->buildViewScriptContext($view);
        $this->buildComponentScriptContext();

        $this->buildResultSessionContext();
        $this->buildResultViewContext();
    }

    public function getViewContext(): array
    {
        return $this->view_context;
    }

    public function getSessionContext(): array
    {
        return $this->session_context;
    }

    private function buildResultViewContext(): void
    {
        $view_context = [
            "url" => __URL__,
            "random" => Cryptography::computeRandomKey(8),
            "headers" =>
            [
                [
                    "name" => "timestamp",
                    "content" => $this->view_timestamp
                ],
                [
                    "name" => "page",
                    "content" => $this->view_identifier
                ]
            ],
            "componentsScripts" => $this->components_scripts,
            "scripts" => $this->scripts,
            "script" => $this->view_script,
            "styles" => $this->styles
        ];

        $this->view_context = Collection::mergeList($view_context, $this->view_context);
        $this->view_context = Collection::mapKeys($this->view_context, function ($key) {
            return "view:" . $key;
        });
    }

    private function buildPackagesContext(): void
    {
        $this->packages[] = new Path(ServerPath::PACKAGES, "jquery-3.6.0/jquery", "min.js");
        $this->packages[] = new Path(ServerPath::PACKAGES, "popper-1.16.1/popper", "min.js");
        $this->packages[] = new Path(ServerPath::PACKAGES, "bootstrap-4.6.0/bootstrap", "min.js");
        $this->packages[] = new Path(ServerPath::PACKAGES, "observable-slim-0.1.5/observable-slim", "min.js");
        $this->packages[] = new Path(ServerPath::PACKAGES, "jquery-validate-1.11.1/jquery.validate", "min.js");
        $this->packages[] = new Path(ServerPath::PACKAGES, "canvas-js/canvasjs", "min.js");
    }

    private function buildStyleContext(): void
    {
        $this->styles[] = "https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100&display=swap";
        $this->styles[] = new Path(ServerPath::PACKAGES, "bootstrap-4.6.0/bootstrap", "css");
        $this->styles[] = new Path(ServerPath::PACKAGES, "font-awesome-4.7.0/font-awesome", "css");
        $this->styles[] = new Path(ServerPath::WEB, "build", "css");
        $this->styles = FileSystem::toWebPaths($this->styles);
    }

    private function buildScriptContext(): void
    {
        $paths = FileSystem::findWebPaths(new DirectoryPath(ServerPath::SRC, "PypeDOM/Boot/Scripts/"), "js");
        $this->scripts = FileSystem::toWebPaths(Collection::mergeList($this->packages, $paths));
    }

    private function buildViewScriptContext(View &$view): void
    {
        $path = $view->getControllerName() . "/" . $view->getViewName();
        $this->view_identifier = $view->getViewGUID();
        $this->view_timestamp = $view->getTimestamp();
        $this->view_script = (new Path(ServerPath::VIEWS, $path, "js"))->toWebPath()->toString();
        $this->view_context = $view->getViewData();
    }

    private function buildResultSessionContext()
    {
        date_default_timezone_set("America/Costa_Rica");
        $this->session_context["now"] = date("m/d/Y h:i:s a", mktime());

        foreach (session()->exposeArray() as $key => $value) {
            $this->session_context["$key"] = $value;
        }

        $this->view_context = Collection::mapKeys($this->view_context, function ($key) {
            return "session:" . $key;
        });
    }

    private function buildComponentScriptContext(): void
    {
        foreach ($this->template_batch->getTemplates() as $template) {
            $name = $template->getComponentName();
            $script = $template->getScripts();

            if (strlen($script) > 0) {
                $temporal = str_replace("function ", "function " . $name . "_", $script);
                $temporal = str_replace("this.state(", "state(id, ", $temporal);
                $this->components_scripts[] = str_replace("this.", $name . "_", $temporal);
            }
        }
    }
}
