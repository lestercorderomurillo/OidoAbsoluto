<?php

namespace Pipeline\DOM;

use Pipeline\Core\DI;
use Pipeline\Core\Types\View;
use Pipeline\FileSystem\FileSystem;
use Pipeline\FileSystem\Path\ServerPath;
use Pipeline\FileSystem\Path\Local\DirectoryPath;
use Pipeline\FileSystem\Path\Local\Path;
use Pipeline\Security\Cryptography;
use Pipeline\Utilities\Collection;

use function Pipeline\Kernel\session;

class PypeDOM
{
    private array $components;

    private array $components_scripts = [];
    private array $components_awake_scripts = [];
    private array $components_stateful_scripts = [];

    private array $view_context = [];
    private array $session_context = [];

    public function __construct()
    {
        $this->loadComponents();
    }

    public function loadView(View &$view): void
    {
        $this->createViewContext($view);
        $this->createSessionContext();
    }

    public function getComponent(string $component_name): Component
    {
        return ($this->components[$component_name]);
    }

    public function getComponents(): array
    {
        return $this->components;
    }

    public function isComponentRegistered(string $component_name): bool
    {
        return (isset($this->components[$component_name]));
    }

    public function registerAwakeScript(string $compiled_script): void
    {
        $this->components_awake_scripts[] = $compiled_script;
    }

    public function registerStatefulScript(string $stateful_script): void
    {
        $this->components_stateful_scripts[] = $stateful_script;
    }

    public function javascriptAction(string $value)
    {
        if (strlen($value) > 0) {
            $value =
                <<<HTML
            <script type="text/javascript">
            $(function() {
            $value
            });
            </script>
            HTML;
        }
        return $value;
    }

    public function getAwakeScriptsOutputHTML($scripts_lines = ""): string
    {
        foreach ($this->components_awake_scripts as $script) {
            $scripts_lines .= $script . "\n";
        }
        return $this->javascriptAction($scripts_lines);
    }

    public function getStatefulScriptsOutputHTML($scripts_lines = ""): string
    {
        $space = str_repeat(' ', 4);
        foreach ($this->components_stateful_scripts as $script) {
            $scripts_lines .= $space . $script . "\n";
        }
        return $this->javascriptAction($scripts_lines);
    }

    public function getComponentScriptsOutputHTML($scripts_lines = ""): string
    {
        foreach ($this->components_scripts as $script) {
            $scripts_lines .= $script . "\n";
        }
        return $this->javascriptAction($scripts_lines);
    }

    public function getViewContext(): array
    {
        return $this->view_context;
    }

    public function getSessionContext(): array
    {
        return $this->session_context;
    }

    private function createViewContext(View $view): void
    {
        $path = $view->getControllerName() . "/" . $view->getViewName();

        $view_identifier = $view->getViewGUID();
        $view_timestamp = $view->getTimestamp();
        $view_script = (new Path(ServerPath::VIEWS, $path, "js"))->toWebPath()->toString();

        $packages = [];
        $packages[] = new Path(ServerPath::PACKAGES, "jquery-3.6.0/jquery", "min.js");
        $packages[] = new Path(ServerPath::PACKAGES, "popper-1.16.1/popper", "min.js");
        $packages[] = new Path(ServerPath::PACKAGES, "bootstrap-4.6.0/bootstrap", "min.js");
        $packages[] = new Path(ServerPath::PACKAGES, "observable-slim-0.1.5/observable-slim", "min.js");
        $packages[] = new Path(ServerPath::PACKAGES, "jquery-validate-1.11.1/jquery.validate", "min.js");
        $packages[] = new Path(ServerPath::PACKAGES, "canvas-js/canvasjs", "min.js");

        $paths = FileSystem::findWebPaths(new DirectoryPath(ServerPath::SRC, "DOM/Runtime/Scripts/"), "js");
        $scripts = FileSystem::toWebPaths(Collection::mergeList($packages, $paths));

        $styles = [];
        $styles[] = "https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100&display=swap";
        $styles[] = new Path(ServerPath::PACKAGES, "bootstrap-4.6.0/bootstrap", "css");
        $styles[] = new Path(ServerPath::PACKAGES, "font-awesome-4.7.0/font-awesome", "css");
        $styles[] = new Path(ServerPath::WEB, "build", "css");
        $styles = FileSystem::toWebPaths($styles);

        $this->loadComponentScripts();

        $this->view_context = Collection::mergeDictionary($view->getViewData(), [
            "url" => __URL__,
            "random" => Cryptography::computeRandomKey(8),
            "headers" =>
            [
                [
                    "name" => "timestamp",
                    "content" => $view_timestamp
                ],
                [
                    "name" => "page",
                    "content" => $view_identifier
                ]
            ],
            "componentsScripts" => $this->components_scripts,
            "script" => $view_script,
            "scripts" => $scripts,
            "styles" => $styles
        ]);

        $this->view_context = Collection::mapKeys($this->view_context, function ($key) {
            return "view:" . $key;
        });
    }

    private function createSessionContext()
    {
        date_default_timezone_set("America/Costa_Rica");
        $this->session_context["now"] = date("m/d/Y h:i:s a", mktime());

        foreach (session()->exposeArray() as $key => $value) {
            $this->session_context["$key"] = $value;
        }

        $this->session_context = Collection::mapKeys($this->session_context, function ($key) {
            return "session:" . $key;
        });
    }

    private function loadComponents(): void
    {
        $component_file_names = FileSystem::find(new DirectoryPath(ServerPath::COMPONENTS));
        $user_component_file_names = FileSystem::find(new DirectoryPath(ServerPath::USERCOMPONENTS));

        $components_to_include = Collection::mergeList($component_file_names, $user_component_file_names);

        $components_imported = [];
        foreach ($components_to_include as $file) {
            $component = require_once($file);
            if (!is_int($component)) {
                $components_imported = Collection::mergeDictionary(false, $component, $components_imported);
            }
        }

        $this->components = [];
        foreach ($components_imported as $name => $definition) {
            $this->components[$name] = new Component($name, $definition);
        }
    }

    private function loadComponentScripts(): void
    {
        $this->components_scripts = [];
        foreach ($this->components as $component) {
            $name = $component->getComponentName();
            $script = $component->getScripts();

            if (strlen($script) > 0) {
                $temporal = str_replace("function ", "function " . $name . "_", $script);
                $temporal = str_replace("this.state(", "state(id, ", $temporal);
                $this->components_scripts[] = str_replace("this.", $name . "_", $temporal);
            }
        }
    }

    public static function createElement(Component $component, array $attributes = [], $context = []): Element
    {
        $element = DI::autowire(Element::class);
        $element->setComponent($component);
        $element->setAttributes($attributes);
        $element->setInheritContext($context);
        return $element;
    }
}
